<?php
// get_qa_curriculum.php
// API endpoint to fetch curriculum proposals for Quality Assurance review

header('Content-Type: application/json');

require_once dirname(__FILE__) . '/../includes/db_connection.php';

try {
    // Get optional status filter
    $statusFilter = $_GET['status'] ?? 'PENDING';
    
    // Validate status filter - map common status names
    $statusMap = [
        'PENDING' => ['Pending QA Review', 'PENDING', 'Draft'],
        'APPROVED' => ['Approved', 'APPROVED'],
        'REJECTED' => ['Rejected', 'REJECTED']
    ];
    
    $validStatuses = ['PENDING', 'APPROVED', 'REJECTED', 'ALL'];
    if (!in_array(strtoupper($statusFilter), $validStatuses)) {
        $statusFilter = 'PENDING';
    }
    
    // Build query based on status filter
    $query = "
        SELECT 
            cp.id as proposal_id,
            cp.user_id,
            cp.program_id,
            cp.term,
            cp.academic_year,
            cp.year_level,
            cp.course_type,
            cp.status,
            cp.courses_data,
            cp.submitted_at,
            cp.reviewed_at as reviewed_at,
            cp.review_notes as review_notes,
            cp.reviewed_by,
            u.first_name,
            u.last_name,
            u.email,
            p.program_code,
            p.program_name,
            d.department_name,
            d.department_code,
            d.color_code
        FROM course_proposals cp
        LEFT JOIN users u ON cp.user_id = u.id
        LEFT JOIN programs p ON cp.program_id = p.id
        LEFT JOIN departments d ON p.department_id = d.id
        WHERE 1=1
    ";
    
    $params = [];
    
    if (strtoupper($statusFilter) !== 'ALL') {
        // Map the filter to actual database status values
        $statusValues = $statusMap[$statusFilter] ?? [$statusFilter];
        $placeholders = implode(',', array_fill(0, count($statusValues), '?'));
        $query .= " AND cp.status IN ($placeholders)";
        $params = array_merge($params, $statusValues);
    }
    
    $query .= " ORDER BY cp.submitted_at DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $proposals = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Process each proposal to extract course data
    $processedProposals = [];
    foreach ($proposals as $proposal) {
        $coursesData = json_decode($proposal['courses_data'] ?? '[]', true);
        
        // Count attachments from courses (if any have file attachments)
        $attachmentsCount = 0;
        foreach ($coursesData as $course) {
            if (isset($course['attachments']) && is_array($course['attachments'])) {
                $attachmentsCount += count($course['attachments']);
            }
        }
        
        // Count references
        $referencesCount = 0;
        foreach ($coursesData as $course) {
            if (isset($course['references']) && is_array($course['references'])) {
                $referencesCount += count($course['references']);
            }
        }
        
        // Get first course info for display
        $firstCourse = !empty($coursesData) ? $coursesData[0] : [];
        
        $processedProposals[] = [
            'proposal_id' => $proposal['proposal_id'],
            'requester_name' => trim(($proposal['first_name'] ?? '') . ' ' . ($proposal['last_name'] ?? '')),
            'requester_email' => $proposal['email'] ?? '',
            'dean_name' => trim(($proposal['first_name'] ?? '') . ' ' . ($proposal['last_name'] ?? '')),
            'department_code' => $proposal['department_code'] ?? '',
            'department_name' => $proposal['department_name'] ?? '',
            'department_color' => $proposal['color_code'] ?? '#1976d2',
            'program_code' => $proposal['program_code'] ?? '',
            'program_name' => $proposal['program_name'] ?? '',
            'course_code' => $firstCourse['course_code'] ?? '',
            'course_name' => $firstCourse['course_title'] ?? $firstCourse['course_name'] ?? '',
            'units' => $firstCourse['units'] ?? '',
            'lecture_hours' => $firstCourse['lecture_hours'] ?? '',
            'laboratory_hours' => $firstCourse['laboratory_hours'] ?? '',
            'prerequisites' => $firstCourse['prerequisites'] ?? '',
            'year_level' => $proposal['year_level'] ?? '',
            'term' => $proposal['term'] ?? '',
            'course_type' => $proposal['course_type'] ?? 'New Course Proposal',
            'status' => $proposal['status'] ?? 'PENDING',
            'courses_data' => $coursesData,
            'course_description' => $firstCourse['course_description'] ?? '',
            'learning_outcomes' => $firstCourse['learning_outcomes'] ?? '',
            'course_outline' => $firstCourse['course_outline'] ?? '',
            'assessment' => $firstCourse['assessment'] ?? '',
            'materials' => $firstCourse['materials'] ?? '',
            'justification' => $firstCourse['justification'] ?? '',
            'references' => $firstCourse['references'] ?? [],
            'attachments' => $firstCourse['attachments'] ?? [],
            'references_count' => $referencesCount,
            'attachments_count' => $attachmentsCount,
            'submitted_at' => $proposal['submitted_at'] ? date('F j, Y', strtotime($proposal['submitted_at'])) : '',
            'reviewed_at' => $proposal['reviewed_at'] ? date('F j, Y', strtotime($proposal['reviewed_at'])) : '',
            'rejection_reason' => $proposal['review_notes'] ?? '',
            'total_courses' => count($coursesData)
        ];
    }
    
    // Get counts for each status
    $countQuery = "
        SELECT 
            status,
            COUNT(*) as count
        FROM course_proposals
        GROUP BY status
    ";
    $countStmt = $pdo->prepare($countQuery);
    $countStmt->execute();
    $statusCounts = $countStmt->fetchAll(PDO::FETCH_ASSOC);
    
    $counts = [
        'PENDING' => 0,
        'APPROVED' => 0,
        'REJECTED' => 0,
        'TOTAL' => 0
    ];
    
    foreach ($statusCounts as $row) {
        $counts[$row['status']] = (int)$row['count'];
        $counts['TOTAL'] += (int)$row['count'];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $processedProposals,
        'counts' => $counts,
        'filter' => $statusFilter
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch curriculum proposals',
        'error' => $e->getMessage()
    ]);
}
?>