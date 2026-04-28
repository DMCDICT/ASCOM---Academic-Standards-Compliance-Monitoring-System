<?php
// program-management.php for Department Dean
// This file is an HTML fragment, included by content.php.
// It will fetch program data from the database and display it.

global $conn; // $conn is provided globally by content.php

// Initialize programs array
$programs = [];
$programHeads = [];

// Try to fetch programs from database
try {
    require_once dirname(__FILE__) . '/../includes/db_connection.php';
    
    // Get the current dean's department code from session
    $deanDepartmentCode = $_SESSION['selected_role']['department_code'] ?? null;
    $deanDepartmentId = $_SESSION['selected_role']['department_id'] ?? null;
    
    if ($deanDepartmentCode) {
        // Fetch programs for the dean's department
        $query = "
            SELECT 
                p.id,
                p.program_code,
                p.program_name,
                p.major,
                d.color_code,
                d.id as department_id,
                p.description,
                COUNT(DISTINCT c.id) as course_count,
                COUNT(DISTINCT u.id) as faculty_count
            FROM 
                programs p
            LEFT JOIN 
                courses c ON p.id = c.program_id
            LEFT JOIN 
                users u ON u.department_id = p.department_id
            JOIN
                departments d ON p.department_id = d.id
            WHERE 
                d.department_code = ?
            GROUP BY 
                p.id, p.program_code, p.program_name, p.major, d.color_code, d.id, p.description
            ORDER BY 
                p.created_at DESC
        ";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$deanDepartmentCode]);
        $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Fetch program heads for these programs
        $programIds = array_column($programs, 'id');
        $programHeads = [];
        
        if (!empty($programIds)) {
            $placeholders = implode(',', array_fill(0, count($programIds), '?'));
            $headQuery = "
                SELECT ph.program_id, ph.teacher_id, u.first_name, u.last_name, u.title
                FROM program_heads ph
                JOIN users u ON ph.teacher_id = u.id
                WHERE ph.program_id IN ($placeholders) AND ph.is_active = TRUE
            ";
            $headStmt = $pdo->prepare($headQuery);
            $headStmt->execute($programIds);
            while ($head = $headStmt->fetch(PDO::FETCH_ASSOC)) {
                $programHeads[$head['program_id']] = $head;
            }
        }
        
    } else {
    }
} catch (Exception $e) {
    $programs = [];
    $programHeads = [];
}

// Store department ID for JavaScript
$deanDepartmentId = $_SESSION['selected_role']['department_id'] ?? null;

// If department_id is not in session, look it up using department_code
if (!$deanDepartmentId && !empty($deanDepartmentCode)) {
    try {
        require_once dirname(__FILE__) . '/../includes/db_connection.php';
        $deptStmt = $pdo->prepare("SELECT id FROM departments WHERE department_code = ?");
        $deptStmt->execute([$deanDepartmentCode]);
        $deptRow = $deptStmt->fetch(PDO::FETCH_ASSOC);
        if ($deptRow) {
            $deanDepartmentId = (int) $deptRow['id'];
        }
    } catch (Exception $e) {
        // Keep deanDepartmentId as null
    }
}

// Show all programs without pagination

$totalPrograms = count($programs);
$totalCourses = 0;
$totalFaculty = 0;
$recentActivities = [];

?>

<div class="pc-page">
    <div class="pc-hero card">
        <div class="pc-hero-main">
            <div class="pc-hero-icon" aria-hidden="true">
                <i data-lucide="folder-cog"></i>
            </div>
            <div>
                <h2 class="pc-title">Programs &amp; Courses</h2>
                <p class="pc-subtitle">Manage programs, assign program heads, and manage course offerings.</p>
            </div>
        </div>
        <div class="pc-hero-actions">
            <button type="button" class="btn-primary" id="addProgramButton">
                <i data-lucide="plus"></i>
                <span>Add Program</span>
            </button>
        </div>
    </div>

    <div class="pc-grid">
        <section class="pc-panel card">
            <div class="section-header pc-section-header">
                <div class="label-bar"></div>
                <div>
                    <h3>Programs</h3>
                    <p>Search programs and jump into course management.</p>
                </div>
            </div>

            <div class="pc-search-row">
                <div class="pc-search">
                    <i data-lucide="search" aria-hidden="true"></i>
                    <input id="pcProgramSearch" type="text" placeholder="Search by code, name, or major…" autocomplete="off" />
                </div>
                <div class="pc-count stat-pill" aria-label="Total programs">
                    <strong><?php echo (int) $totalPrograms; ?></strong> programs
                </div>
            </div>

            <div class="pc-programs" id="programContainer">
                <?php if (!empty($programs)) : ?>
                    <?php 
                    $index = 0;
                    foreach ($programs as $program) :
                        $programId = (int) $program['id'];
                        $head = $programHeads[$programId] ?? null;
                        $headName = $head ? trim(($head['title'] ?? '') . ' ' . $head['first_name'] . ' ' . $head['last_name']) : null;
                        $programCode = (string) $program['program_code'];
                        $programNameSafe = (string) ($program['program_name'] ?? '');
                        $programMajorSafe = (string) ($program['major'] ?? '');
                        $programColorSafe = (string) ($program['color_code'] ?? '#0C4B34');
                        $programDescriptionSafe = (string) ($program['description'] ?? '');
                        $courseCount = (int) ($program['course_count'] ?? 0);
                        $facultyCount = (int) ($program['faculty_count'] ?? 0);
                        $searchBlob = strtolower(trim($programCode . ' ' . $programNameSafe . ' ' . $programMajorSafe));
                        $delay = 0.16 + ($index * 0.08);
                        $index++;
                    ?>
                        <article class="pc-program" data-program-search="<?php echo htmlspecialchars($searchBlob); ?>" style="animation-delay: <?php echo $delay; ?>s">
                            <div class="pc-program-top">
                                <div class="pc-program-code code-badge" style="background-color: <?php echo htmlspecialchars($programColorSafe); ?>">
                                    <?php echo htmlspecialchars($programCode); ?>
                                </div>
                                <div class="pc-program-meta">
                                    <div class="pc-program-name"><?php echo htmlspecialchars($programNameSafe); ?></div>
                                    <?php if ($programMajorSafe !== '') : ?>
                                        <div class="pc-program-major">Major in <strong><?php echo htmlspecialchars($programMajorSafe); ?></strong></div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <?php if ($programDescriptionSafe !== '') : ?>
                                <div class="pc-program-desc"><?php echo htmlspecialchars($programDescriptionSafe); ?></div>
                            <?php endif; ?>

                            <div class="pc-program-stats">
                                <div class="stat-pill"><strong><?php echo $courseCount; ?></strong> courses</div>
                                <div class="stat-pill"><strong><?php echo $facultyCount; ?></strong> faculty</div>
                            </div>

                            <div class="pc-program-head">
                                <div class="pc-program-head-label">Program Head</div>
                                <div class="pc-program-head-value">
                                    <?php if ($headName) : ?>
                                        <span class="pc-head-name"><?php echo htmlspecialchars($headName); ?></span>
                                        <button type="button" class="pc-head-remove" onclick="removeProgramHead(<?php echo $programId; ?>)">Remove</button>
                                    <?php else : ?>
                                        <span class="pc-head-empty">Not assigned</span>
                                    <?php endif; ?>
                                </div>
                                <button type="button" class="pc-head-assign" onclick='openAssignProgramHeadModal(<?php echo $programId; ?>, <?php echo json_encode($programNameSafe); ?>)' style="margin-top: 8px;">
                                    <?php echo $headName ? 'Change Program Head' : 'Assign Program Head'; ?>
                                </button>
                            </div>

                            <div class="pc-program-actions">
                                <a class="pc-link btn-primary" href="content.php?page=program-courses&amp;program=<?php echo urlencode($programCode); ?>">
                                    <i data-lucide="list"></i>
                                    <span>Manage Courses</span>
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php else : ?>
                    <div class="pc-empty">
                        <div class="pc-empty-icon icon-container" aria-hidden="true"><i data-lucide="folder-plus"></i></div>
                        <div class="pc-empty-title">No programs yet</div>
                        <div class="pc-empty-subtitle">Create your first program to start organizing courses.</div>
                        <button type="button" class="btn-primary" onclick="openAddProgramModal()">
                            <i data-lucide="plus"></i>
                            <span>Create First Program</span>
                        </button>
                    </div>
                <?php endif; ?>
</div>
        </section>
    </div>
</div>

<script>
    const programs = <?php echo json_encode($programs); ?>;
    const recentActivities = <?php echo json_encode($recentActivities); ?>;
    const deanDepartmentId = <?php echo json_encode($deanDepartmentId); ?>;

    (function initProgramsCoursesPage() {
        const searchInput = document.getElementById('pcProgramSearch');
        if (!searchInput) return;

        const cards = Array.from(document.querySelectorAll('#programContainer .pc-program'));
        searchInput.addEventListener('input', function() {
            const query = (searchInput.value || '').trim().toLowerCase();
            cards.forEach(card => {
                const haystack = card.getAttribute('data-program-search') || '';
                const match = query === '' ? true : haystack.includes(query);
                card.style.display = match ? '' : 'none';
            });
        });
    })();
    
    // Program Head Assignment Functions
    function openAssignProgramHeadModal(programId, programName) {
        // Load teachers from dean's department and show modal
        const modalHtml = `
            <div id="assignProgramHeadModal" class="modal-overlay" style="display: flex; position: fixed; z-index: 10003; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center;">
                <div class="modal-box" style="background: white; border-radius: 12px; padding: 24px; width: 90%; max-width: 450px; max-height: 80vh; overflow-y: auto;">
                    <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 12px;">
                        <h2 style="margin: 0; font-size: 18px; color: #0C4B34;">Assign Program Head</h2>
                        <span class="close-button" onclick="closeAssignProgramHeadModal()" style="font-size: 24px; cursor: pointer; color: #666;">&times;</span>
                    </div>
                    <p style="margin: 0 0 15px 0; color: #666; font-size: 14px;">Select a teacher from your department to assign as program head for <strong>${programName}</strong></p>
                    <div id="programHeadTeacherList" style="max-height: 300px; overflow-y: auto; border: 1px solid #eee; border-radius: 8px;">
                        <div style="padding: 20px; text-align: center; color: #999;">Loading teachers...</div>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing modal if any
        const existingModal = document.getElementById('assignProgramHeadModal');
        if (existingModal) existingModal.remove();
        
        // Add modal to body
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        
        // Load teachers
        loadTeachersForProgramHead(programId);
    }
    
    function closeAssignProgramHeadModal() {
        const modal = document.getElementById('assignProgramHeadModal');
        if (modal) modal.remove();
    }
    
    function loadTeachersForProgramHead(programId) {
        if (!deanDepartmentId) {
            document.getElementById('programHeadTeacherList').innerHTML = 
                '<div style="padding: 20px; text-align: center; color: #dc3545;">Department not found</div>';
            return;
        }
        
        fetch(`api/get_department_teachers.php?department_id=${deanDepartmentId}`)
            .then(res => res.json())
            .then(data => {
                const container = document.getElementById('programHeadTeacherList');
                
                if (!data.success || !data.teachers || data.teachers.length === 0) {
                    container.innerHTML = '<div style="padding: 20px; text-align: center; color: #999;">No teachers found in your department</div>';
                    return;
                }
                
                container.innerHTML = data.teachers.map(teacher => {
                    const fullName = (teacher.title ? teacher.title + ' ' : '') + teacher.first_name + ' ' + teacher.last_name;
                    const isHead = teacher.is_program_head ? '<span style="color: #0C4B34; font-size: 11px; display: block; margin-top: 4px;">Currently Program Head</span>' : '';
                    
                    return `
                        <div class="teacher-option" onclick="assignProgramHead(${programId}, ${teacher.id}, '${fullName.replace(/'/g, "\\'")}')" 
                             style="padding: 12px 16px; border-bottom: 1px solid #f0f0f0; cursor: pointer; transition: background 0.2s;">
                            <div style="font-weight: 600; color: #333; font-size: 14px;">${fullName}</div>
                            <div style="color: #666; font-size: 12px;">${teacher.employee_no || 'No employee number'}</div>
                            ${isHead}
                        </div>
                    `;
                }).join('');
                
                container.innerHTML += `
                    <div style="padding: 12px 16px; border-top: 1px solid #eee; background: #f9f9f9;">
                        <button onclick="closeAssignProgramHeadModal()" style="width: 100%; padding: 10px; background: #eee; border: none; border-radius: 6px; cursor: pointer; font-size: 13px;">Cancel</button>
                    </div>
                `;
            })
            .catch(err => {
                document.getElementById('programHeadTeacherList').innerHTML = 
                    '<div style="padding: 20px; text-align: center; color: #dc3545;">Error loading teachers</div>';
            });
    }
    
    function assignProgramHead(programId, teacherId, teacherName) {
        if (!confirm(`Are you sure you want to assign ${teacherName} as the program head?`)) {
            return;
        }
        
        fetch('api/assign_program_head.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ program_id: programId, teacher_id: teacherId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Program head assigned successfully!');
                closeAssignProgramHeadModal();
                location.reload();
            } else {
                alert(data.message || 'Failed to assign program head');
            }
        })
        .catch(err => {
            alert('Error assigning program head');
        });
    }
    
    function removeProgramHead(programId) {
        if (!confirm('Are you sure you want to remove this program head?')) {
            return;
        }
        
        fetch('api/remove_program_head.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ program_id: programId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Program head removed successfully!');
                location.reload();
            } else {
                alert(data.message || 'Failed to remove program head');
            }
        })
        .catch(err => {
            alert('Error removing program head');
        });
    }
</script>
