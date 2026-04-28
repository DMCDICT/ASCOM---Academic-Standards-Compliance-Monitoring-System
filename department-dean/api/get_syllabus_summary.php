<?php
// get_syllabus_summary.php - Get syllabus summary for dean dashboard
require_once dirname(__FILE__) . '/../../session_config.php';
require_once dirname(__FILE__) . '/../../bootstrap/auth.php';

// Check for dean role
ascom_require_role('dean', dirname(__FILE__) . '/../../user_login.php');

header('Content-Type: application/json');

try {
    require_once dirname(__FILE__) . '/../../super_admin-mis/includes/db_connection.php';
    
    $department_id = $_SESSION['selected_role']['department_id'] ?? null;
    $academic_year = $_SESSION['selected_role']['academic_year'] ?? null;
    $term = $_SESSION['selected_role']['term'] ?? null;
    
    if (!$department_id) {
        echo json_encode(['success' => false, 'message' => 'Department not found']);
        exit;
    }
    
    // Get summary stats
    $statsQuery = "
        SELECT 
            COUNT(DISTINCT c.id) as total_courses,
            COUNT(DISTINCT cs.id) as syllabi_total,
            SUM(CASE WHEN cs.status = 'ph_approved' THEN 1 ELSE 0 END) as syllabi_ph_approved,
            SUM(CASE WHEN cs.status = 'dean_approved' THEN 1 ELSE 0 END) as syllabi_dean_approved,
            SUM(CASE WHEN cs.status = 'submitted' THEN 1 ELSE 0 END) as syllabi_pending_ph,
            SUM(CASE WHEN cs.status = 'revision_requested' THEN 1 ELSE 0 END) as syllabi_needs_revision,
            SUM(CASE WHEN cs.status = 'draft' OR cs.status IS NULL THEN 1 ELSE 0 END) as syllabi_not_started
        FROM courses c
        LEFT JOIN course_syllabi cs ON c.id = cs.course_id
        WHERE c.department_id = ?
    ";
    
    $statsStmt = $pdo->prepare($statsQuery);
    $statsStmt->execute([$department_id]);
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
    
    // Get syllabi needing dean's attention (PH approved, ready for final review)
    $pendingDeanQuery = "
        SELECT 
            cs.id,
            cs.course_id,
            c.course_code,
            c.course_title,
            u.first_name,
            u.last_name,
            u.title,
            cs.status,
            cs.submitted_at,
            cs.ph_approved_at,
            p.program_name,
            ph.first_name as ph_first_name,
            ph.last_name as ph_last_name,
            ph.title as ph_title,
            cs.ph_review_comments
        FROM course_syllabi cs
        JOIN courses c ON cs.course_id = c.id
        JOIN users u ON cs.teacher_id = u.id
        LEFT JOIN program_heads ph ON ph.program_id = c.program_id AND ph.is_active = TRUE
        LEFT JOIN users ph_u ON ph.teacher_id = ph_u.id
        LEFT JOIN programs p ON c.program_id = p.id
        WHERE c.department_id = ? AND cs.status = 'ph_approved'
        ORDER BY cs.ph_approved_at ASC
    ";
    
    $pendingDeanStmt = $pdo->prepare($pendingDeanQuery);
    $pendingDeanStmt->execute([$department_id]);
    $pendingDeanApproval = $pendingDeanStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get all submitted syllabi for monitoring
    $allSubmittedQuery = "
        SELECT 
            cs.id,
            cs.course_id,
            c.course_code,
            c.course_title,
            u.first_name,
            u.last_name,
            u.title,
            cs.status,
            cs.submitted_at,
            cs.ph_approved_at,
            cs.dean_approved_at,
            cs.ph_review_comments,
            cs.dean_review_comments,
            p.program_name
        FROM course_syllabi cs
        JOIN courses c ON cs.course_id = c.id
        JOIN users u ON cs.teacher_id = u.id
        LEFT JOIN programs p ON c.program_id = p.id
        WHERE c.department_id = ?
        ORDER BY 
            CASE cs.status
                WHEN 'revision_requested' THEN 1
                WHEN 'submitted' THEN 2
                WHEN 'ph_review' THEN 3
                WHEN 'ph_approved' THEN 4
                WHEN 'dean_approved' THEN 5
                ELSE 6
            END,
            cs.submitted_at DESC
    ";
    
    $allSubmittedStmt = $pdo->prepare($allSubmittedQuery);
    $allSubmittedStmt->execute([$department_id]);
    $allSyllabi = $allSubmittedStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format teacher names
    foreach ($pendingDeanApproval as &$s) {
        $s['teacher_name'] = ($s['title'] ? $s['title'] . ' ' : '') . $s['first_name'] . ' ' . $s['last_name'];
        $s['ph_name'] = ($s['ph_title'] ? $s['ph_title'] . ' ' : '') . ($s['ph_first_name'] ?? '') . ' ' . ($s['ph_last_name'] ?? '');
    }
    
    foreach ($allSyllabi as &$s) {
        $s['teacher_name'] = ($s['title'] ? $s['title'] . ' ' : '') . $s['first_name'] . ' ' . $s['last_name'];
    }
    
    echo json_encode([
        'success' => true,
        'stats' => $stats,
        'pendingDeanApproval' => $pendingDeanApproval,
        'allSyllabi' => $allSyllabi
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error loading summary: ' . $e->getMessage()]);
}
?>