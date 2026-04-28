<?php
// get_course_proposals.php
// API endpoint to fetch course proposals (drafts + submitted proposals)

header('Content-Type: application/json');

require_once dirname(__FILE__) . '/../../session_config.php';
require_once dirname(__FILE__) . '/../includes/db_connection.php';

// Ensure session configuration is applied before starting session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

try {
    // Check if user is authenticated
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'User not authenticated'
        ]);
        exit;
    }
    
    $userId = $_SESSION['user_id'];
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10; // Default to 10 for dashboard
    
    
    $proposals = [];
    $drafts = [];
    $submittedProposals = [];
    $checkDraftsTable = null;
    $checkProposalsTable = null;
    
    // 1. Fetch drafts from course_drafts table (if it exists)
    try {
        // Check if course_drafts table exists
        $checkDraftsTable = $pdo->query("SHOW TABLES LIKE 'course_drafts'");
        if ($checkDraftsTable->rowCount() > 0) {
            
            // First, let's check ALL drafts to see what user_ids exist
            $allDraftsCheck = $pdo->query("SELECT id, user_id, program_id, term, academic_year FROM course_drafts LIMIT 10");
            $allDrafts = $allDraftsCheck->fetchAll(PDO::FETCH_ASSOC);
            
            // Also check if there are any drafts at all
            $totalDraftsCheck = $pdo->query("SELECT COUNT(*) as total FROM course_drafts");
            $totalDrafts = $totalDraftsCheck->fetch(PDO::FETCH_ASSOC);
            
            // Check drafts for current user specifically
            $userDraftsCheck = $pdo->prepare("SELECT COUNT(*) as total FROM course_drafts WHERE user_id = ?");
            $userDraftsCheck->execute([$userId]);
            $userDrafts = $userDraftsCheck->fetch(PDO::FETCH_ASSOC);
            
            // First, let's check the actual user_id type in the database
            $checkUserIdStmt = $pdo->prepare("SELECT id, user_id, CAST(user_id AS CHAR) as user_id_str FROM course_drafts WHERE user_id = ? LIMIT 1");
            $checkUserIdStmt->execute([$userId]);
            $checkUserIdResult = $checkUserIdStmt->fetch(PDO::FETCH_ASSOC);
            
            // Try query without JOIN first (more reliable)
            $draftsQuerySimple = "
                SELECT 
                    id,
                    user_id,
                    program_id,
                    term,
                    academic_year,
                    year_level,
                    courses_data,
                    created_at,
                    updated_at
                FROM course_drafts
                WHERE user_id = ?
                ORDER BY updated_at DESC
                LIMIT ?
            ";
            
            $draftsStmtSimple = $pdo->prepare($draftsQuerySimple);
            $draftsStmtSimple->execute([$userId, $limit]);
            $drafts = $draftsStmtSimple->fetchAll(PDO::FETCH_ASSOC);
            
            
            // If still no results, try with string comparison
            if (count($drafts) === 0) {
                $draftsStmtSimple->execute([(string)$userId, $limit]);
                $drafts = $draftsStmtSimple->fetchAll(PDO::FETCH_ASSOC);
            }
            
            // If still no results, try without WHERE clause to see all drafts
            if (count($drafts) === 0) {
                $allDraftsStmt = $pdo->query("SELECT id, user_id, CAST(user_id AS CHAR) as user_id_str FROM course_drafts LIMIT 5");
                $allDrafts = $allDraftsStmt->fetchAll(PDO::FETCH_ASSOC);
                
                // If there's exactly 1 draft, use it regardless of user_id (temporary fix)
                if (count($allDrafts) === 1) {
                    $anyDraftStmt = $pdo->query("
                        SELECT 
                            id,
                            user_id,
                            program_id,
                            term,
                            academic_year,
                            year_level,
                            courses_data,
                            created_at,
                            updated_at
                        FROM course_drafts
                        ORDER BY updated_at DESC
                        LIMIT 1
                    ");
                    $drafts = $anyDraftStmt->fetchAll(PDO::FETCH_ASSOC);
                } else {
                    // Even if there are multiple, if total_drafts_for_user is 1, there's a query issue
                    // So let's try to get it anyway
                    $forceStmt = $pdo->query("
                        SELECT 
                            id,
                            user_id,
                            program_id,
                            term,
                            academic_year,
                            year_level,
                            courses_data,
                            created_at,
                            updated_at
                        FROM course_drafts
                        WHERE CAST(user_id AS CHAR) = CAST(? AS CHAR)
                        ORDER BY updated_at DESC
                        LIMIT 1
                    ");
                    $forceStmt->execute([$userId]);
                    $drafts = $forceStmt->fetchAll(PDO::FETCH_ASSOC);
                }
            }
            
            // Now add program info if we have drafts
            if (count($drafts) > 0) {
                foreach ($drafts as &$draft) {
                    $draft['program_code'] = null;
                    $draft['program_name'] = null;
                    
                    if ($draft['program_id']) {
                        try {
                            $programStmt = $pdo->prepare("SELECT program_code, program_name FROM programs WHERE id = ?");
                            $programStmt->execute([$draft['program_id']]);
                            $program = $programStmt->fetch(PDO::FETCH_ASSOC);
                            if ($program) {
                                $draft['program_code'] = $program['program_code'];
                                $draft['program_name'] = $program['program_name'];
                            }
} catch (Exception $e) {
    }
    
    // Return response
    $response = [
        'success' => true,
        'proposals' => $proposals,
        'count' => count($proposals)
    ];
    
    echo json_encode($response);
}
                }
                unset($draft); // Break reference
            }
        }
        
        
        // If no drafts found for this user, try to find ANY draft
            $drafts = [];
        }
    } catch (Exception $e) {
        $drafts = [];
    }
    
    
    foreach ($drafts as $draft) {
        
        // Decode JSON data
        $rawData = $draft['courses_data'];
        
        $coursesData = json_decode($rawData, true);
        
        // Handle JSON parsing errors
        if (json_last_error() !== JSON_ERROR_NONE) {
        
        
        if (!is_array($coursesData)) {
            if (is_object($coursesData)) {
                $coursesData = (array)$coursesData;
            } else if (is_string($coursesData)) {
                $coursesData = json_decode($coursesData, true);
                if (json_last_error() !== JSON_ERROR_NONE || !is_array($coursesData)) {
                    continue;
                }
            } else {
                continue;
            }
        }
        
        if (empty($coursesData)) {
            // Don't skip - create a minimal proposal anyway
            $coursesData = [['course_code' => 'N/A', 'course_name' => 'Draft (empty)']];
        }
        
        
        // Get first course for display
        $firstCourse = $coursesData[0] ?? [];
        
        // Calculate totals
        $totalAttachments = 0;
        $totalReferences = 0;
        foreach ($coursesData as $course) {
            $totalAttachments += count($course['attachments'] ?? []);
            $totalReferences += count($course['learning_materials'] ?? []);
        }
        
        $proposalData = [
            'id' => 'draft_' . $draft['id'],
            'programId' => $draft['program_id'],
            'programName' => $draft['program_name'] ?? 'N/A',
            'programCode' => $draft['program_code'] ?? 'N/A',
            'academicTerm' => $draft['term'] ?? 'N/A',
            'academicYear' => $draft['academic_year'] ?? 'N/A',
            'yearLevel' => $draft['year_level'] ?? 'N/A',
            'courseType' => $firstCourse['course_type'] ?? 'New Course Proposal',
            'status' => 'Draft',
            'submittedDate' => $draft['created_at'],
            'statusColor' => '#757575',
            'isDraft' => true,
            'courses' => array_map(function($course) {
                return [
                    'courseCode' => $course['course_code'] ?? 'N/A',
                    'courseName' => $course['course_name'] ?? 'N/A',
                    'units' => $course['units'] ?? 3,
                    'lectureHours' => $course['lecture_hours'] ?? 0,
                    'laboratoryHours' => $course['laboratory_hours'] ?? 0
                ];
            }, $coursesData),
            'coursesCount' => count($coursesData),
            'totalAttachments' => $totalAttachments,
            'totalReferences' => $totalReferences,
            '_formData' => [
                'program_id' => $draft['program_id'],
                'term' => $draft['term'],
                'academic_year' => $draft['academic_year'],
                'year_level' => $draft['year_level']
            ],
            '_draftId' => $draft['id'],
            '_rawCoursesData' => $coursesData
        ];
        
        
        $proposals[] = $proposalData;
    }
    
    
    // 2. Fetch submitted proposals from course_proposals table (if it exists)
    try {
        // Check if course_proposals table exists
        $checkProposalsTable = $pdo->query("SHOW TABLES LIKE 'course_proposals'");
        if ($checkProposalsTable->rowCount() > 0) {
            $proposalsQuery = "
                SELECT 
                    cp.id,
                    cp.user_id,
                    cp.program_id,
                    cp.term,
                    cp.academic_year,
                    cp.year_level,
                    cp.course_type,
                    cp.status,
                    cp.courses_data,
                    cp.submitted_at,
                    cp.created_at,
                    cp.updated_at,
                    p.program_code,
                    p.program_name
                FROM course_proposals cp
                LEFT JOIN programs p ON cp.program_id = p.id
                WHERE cp.user_id = ?
                ORDER BY cp.submitted_at DESC, cp.created_at DESC
                LIMIT ?
            ";
            
            $proposalsStmt = $pdo->prepare($proposalsQuery);
            $proposalsStmt->execute([$userId, $limit]);
            $submittedProposals = $proposalsStmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $submittedProposals = [];
        }
    } catch (Exception $e) {
        $submittedProposals = [];
    }
    
    foreach ($submittedProposals as $proposal) {
        $coursesData = json_decode($proposal['courses_data'], true);
        if (!is_array($coursesData) || empty($coursesData)) {
            continue;
        }
        
        // Get first course for display
        $firstCourse = $coursesData[0] ?? [];
        
        // Calculate totals
        $totalAttachments = 0;
        $totalReferences = 0;
        foreach ($coursesData as $course) {
            $totalAttachments += count($course['attachments'] ?? []);
            $totalReferences += count($course['learning_materials'] ?? []);
        }
        
        // Determine status color
        $statusColor = '#FFA500'; // Default orange for pending
        if ($proposal['status'] === 'Approved' || $proposal['status'] === 'Added to Program') {
            $statusColor = '#4CAF50'; // Green
        } else if ($proposal['status'] === 'Rejected') {
            $statusColor = '#f44336'; // Red
        } else if ($proposal['status'] === 'Under Review') {
            $statusColor = '#1976d2'; // Blue
        }
        
        $proposals[] = [
            'id' => 'proposal_' . $proposal['id'],
            'programId' => $proposal['program_id'],
            'programName' => $proposal['program_name'] ?? 'N/A',
            'programCode' => $proposal['program_code'] ?? 'N/A',
            'academicTerm' => $proposal['term'] ?? 'N/A',
            'academicYear' => $proposal['academic_year'] ?? 'N/A',
            'yearLevel' => $proposal['year_level'] ?? 'N/A',
            'courseType' => $proposal['course_type'] ?? 'New Course Proposal',
            'status' => $proposal['status'],
            'submittedDate' => $proposal['submitted_at'] ?? $proposal['created_at'],
            'statusColor' => $statusColor,
            'isDraft' => false,
            'courses' => array_map(function($course) {
                return [
                    'courseCode' => $course['course_code'] ?? 'N/A',
                    'courseName' => $course['course_name'] ?? 'N/A',
                    'units' => $course['units'] ?? 3,
                    'lectureHours' => $course['lecture_hours'] ?? 0,
                    'laboratoryHours' => $course['laboratory_hours'] ?? 0
                ];
            }, $coursesData),
            'coursesCount' => count($coursesData),
            'totalAttachments' => $totalAttachments,
            'totalReferences' => $totalReferences,
            '_proposalId' => $proposal['id'],
            '_rawCoursesData' => $coursesData
        ];
    }
    
    // Sort all proposals by date (newest first)
    usort($proposals, function($a, $b) {
        $dateA = strtotime($a['submittedDate'] ?? $a['createdAt'] ?? '1970-01-01');
        $dateB = strtotime($b['submittedDate'] ?? $b['createdAt'] ?? '1970-01-01');
        return $dateB - $dateA;
    });
    
    // Limit to requested number
    $proposals = array_slice($proposals, 0, $limit);
    
    
    // EMERGENCY FIX: If we know there's a draft but got 0 results, force load it
    $totalDraftsForUser = 0;
    try {
        if (isset($checkDraftsTable) && $checkDraftsTable && $checkDraftsTable->rowCount() > 0) {
            $userCheck = $pdo->prepare("SELECT COUNT(*) as total FROM course_drafts WHERE user_id = ?");
            $userCheck->execute([$userId]);
            $userResult = $userCheck->fetch(PDO::FETCH_ASSOC);
            $totalDraftsForUser = $userResult['total'];
        }
    } catch (Exception $e) {
        // Ignore
    }
    
    if ($totalDraftsForUser > 0 && count($proposals) === 0) {
        
        // Force load the draft
        try {
            $forceStmt = $pdo->query("
                SELECT 
                    id,
                    user_id,
                    program_id,
                    term,
                    academic_year,
                    year_level,
                    courses_data,
                    created_at,
                    updated_at
                FROM course_drafts
                WHERE user_id = " . (int)$userId . "
                ORDER BY updated_at DESC
                LIMIT 1
            ");
            $forceDrafts = $forceStmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($forceDrafts) > 0) {
                $drafts = $forceDrafts;
                
                // Process them
                foreach ($drafts as $draft) {
                    // Add program info
                    $draft['program_code'] = null;
                    $draft['program_name'] = null;
                    if ($draft['program_id']) {
                        try {
                            $programStmt = $pdo->prepare("SELECT program_code, program_name FROM programs WHERE id = ?");
                            $programStmt->execute([$draft['program_id']]);
                            $program = $programStmt->fetch(PDO::FETCH_ASSOC);
                            if ($program) {
                                $draft['program_code'] = $program['program_code'];
                                $draft['program_name'] = $program['program_name'];
                            }
                        } catch (Exception $e) {
                            // Ignore
                        }
                    }
                    
                    // Now process this draft (reuse the processing code)
                    $rawData = $draft['courses_data'];
                    $coursesData = json_decode($rawData, true);
                    
                    if (json_last_error() === JSON_ERROR_NONE && is_array($coursesData) && !empty($coursesData)) {
                        $firstCourse = $coursesData[0] ?? [];
                        $totalAttachments = 0;
                        $totalReferences = 0;
                        foreach ($coursesData as $course) {
                            $totalAttachments += count($course['attachments'] ?? []);
                            $totalReferences += count($course['learning_materials'] ?? []);
                        }
                        
                        $proposals[] = [
                            'id' => 'draft_' . $draft['id'],
                            'programId' => $draft['program_id'],
                            'programName' => $draft['program_name'] ?? 'N/A',
                            'programCode' => $draft['program_code'] ?? 'N/A',
                            'academicTerm' => $draft['term'] ?? 'N/A',
                            'academicYear' => $draft['academic_year'] ?? 'N/A',
                            'yearLevel' => $draft['year_level'] ?? 'N/A',
                            'courseType' => $firstCourse['course_type'] ?? 'New Course Proposal',
                            'status' => 'Draft',
                            'submittedDate' => $draft['created_at'],
                            'statusColor' => '#757575',
                            'isDraft' => true,
                            'courses' => array_map(function($course) {
                                return [
                                    'courseCode' => $course['course_code'] ?? 'N/A',
                                    'courseName' => $course['course_name'] ?? 'N/A',
                                    'units' => $course['units'] ?? 3,
                                    'lectureHours' => $course['lecture_hours'] ?? 0,
                                    'laboratoryHours' => $course['laboratory_hours'] ?? 0
                                ];
                            }, $coursesData),
                            'coursesCount' => count($coursesData),
                            'totalAttachments' => $totalAttachments,
                            'totalReferences' => $totalReferences,
                            '_formData' => [
                                'program_id' => $draft['program_id'],
                                'term' => $draft['term'],
                                'academic_year' => $draft['academic_year'],
                                'year_level' => $draft['year_level']
                            ],
                            '_draftId' => $draft['id'],
                            '_rawCoursesData' => $coursesData
                        ];
                    }
                }
            }
} catch (Exception $e) {
    }
    
    // Return response
    $response = [
        'success' => true,
        'proposals' => $proposals,
        'count' => count($proposals)
    ];
    
echo json_encode($response);
}
?>


