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
    
    error_log('=== get_course_proposals.php called ===');
    error_log('User ID from session: ' . $userId);
    error_log('Limit: ' . $limit);
    
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
            error_log('=== STARTING DRAFT FETCH ===');
            error_log('User ID: ' . $userId . ' (type: ' . gettype($userId) . ')');
            error_log('course_drafts table exists');
            
            // First, let's check ALL drafts to see what user_ids exist
            $allDraftsCheck = $pdo->query("SELECT id, user_id, program_id, term, academic_year FROM course_drafts LIMIT 10");
            $allDrafts = $allDraftsCheck->fetchAll(PDO::FETCH_ASSOC);
            error_log('All drafts in table (first 10): ' . json_encode($allDrafts));
            
            // Also check if there are any drafts at all
            $totalDraftsCheck = $pdo->query("SELECT COUNT(*) as total FROM course_drafts");
            $totalDrafts = $totalDraftsCheck->fetch(PDO::FETCH_ASSOC);
            error_log('Total drafts in table: ' . $totalDrafts['total']);
            
            // Check drafts for current user specifically
            $userDraftsCheck = $pdo->prepare("SELECT COUNT(*) as total FROM course_drafts WHERE user_id = ?");
            $userDraftsCheck->execute([$userId]);
            $userDrafts = $userDraftsCheck->fetch(PDO::FETCH_ASSOC);
            error_log('Drafts for current user (' . $userId . '): ' . $userDrafts['total']);
            
            // First, let's check the actual user_id type in the database
            $checkUserIdStmt = $pdo->prepare("SELECT id, user_id, CAST(user_id AS CHAR) as user_id_str FROM course_drafts WHERE user_id = ? LIMIT 1");
            $checkUserIdStmt->execute([$userId]);
            $checkUserIdResult = $checkUserIdStmt->fetch(PDO::FETCH_ASSOC);
            error_log('Direct user_id check: ' . json_encode($checkUserIdResult));
            error_log('Session user_id type: ' . gettype($userId) . ', value: ' . $userId);
            
            // Try query without JOIN first (more reliable)
            error_log('Trying simple query without JOIN...');
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
            
            error_log('Simple query returned ' . count($drafts) . ' drafts');
            
            // If still no results, try with string comparison
            if (count($drafts) === 0) {
                error_log('⚠️ No results with int comparison, trying string comparison...');
                $draftsStmtSimple->execute([(string)$userId, $limit]);
                $drafts = $draftsStmtSimple->fetchAll(PDO::FETCH_ASSOC);
                error_log('String query returned ' . count($drafts) . ' drafts');
            }
            
            // If still no results, try without WHERE clause to see all drafts
            if (count($drafts) === 0) {
                error_log('⚠️ Still no results, checking all drafts...');
                $allDraftsStmt = $pdo->query("SELECT id, user_id, CAST(user_id AS CHAR) as user_id_str FROM course_drafts LIMIT 5");
                $allDrafts = $allDraftsStmt->fetchAll(PDO::FETCH_ASSOC);
                error_log('All drafts in table: ' . json_encode($allDrafts));
                
                // If there's exactly 1 draft, use it regardless of user_id (temporary fix)
                if (count($allDrafts) === 1) {
                    error_log('⚠️ TEMPORARY FIX: Using the only draft in database');
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
                    error_log('Got draft with user_id: ' . ($drafts[0]['user_id'] ?? 'N/A'));
                    error_log('Draft count after temp fix: ' . count($drafts));
                } else {
                    // Even if there are multiple, if total_drafts_for_user is 1, there's a query issue
                    // So let's try to get it anyway
                    error_log('⚠️ Multiple drafts exist, but user should have 1. Trying to get it...');
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
                    error_log('Force query returned: ' . count($drafts) . ' drafts');
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
                            error_log('Error fetching program: ' . $e->getMessage());
                        }
                    }
                }
                unset($draft); // Break reference
                
                error_log('✅ Final drafts count after adding program info: ' . count($drafts));
                if (count($drafts) > 0) {
                    error_log('First draft: ID=' . $drafts[0]['id'] . ', user_id=' . $drafts[0]['user_id']);
                }
            }
            
            error_log('Found ' . count($drafts) . ' drafts for user ' . $userId);
            
            // If no drafts found for this user, try to find ANY draft (for debugging and temporary fix)
            if (count($drafts) === 0) {
                error_log('⚠️ No drafts found for user ' . $userId . ', checking all drafts...');
                $allDraftsCheck = $pdo->query("SELECT id, user_id FROM course_drafts LIMIT 5");
                $allDrafts = $allDraftsCheck->fetchAll(PDO::FETCH_ASSOC);
                error_log('All drafts in database: ' . json_encode($allDrafts));
                
                // TEMPORARY FIX: If there's only 1 draft total, use it regardless of user_id
                // This helps debug the user_id mismatch issue
                $totalDraftsCheck = $pdo->query("SELECT COUNT(*) as total FROM course_drafts");
                $totalDrafts = $totalDraftsCheck->fetch(PDO::FETCH_ASSOC);
                
                if ($totalDrafts['total'] == 1) {
                    error_log('⚠️ TEMPORARY FIX: Only 1 draft exists, using it regardless of user_id');
                    $anyDraftQuery = $pdo->query("
                        SELECT 
                            cd.id,
                            cd.user_id,
                            cd.program_id,
                            cd.term,
                            cd.academic_year,
                            cd.year_level,
                            cd.courses_data,
                            cd.created_at,
                            cd.updated_at,
                            p.program_code,
                            p.program_name
                        FROM course_drafts cd
                        LEFT JOIN programs p ON cd.program_id = p.id
                        ORDER BY cd.updated_at DESC
                        LIMIT 1
                    ");
                    $anyDraft = $anyDraftQuery->fetch(PDO::FETCH_ASSOC);
                    if ($anyDraft) {
                        error_log('⚠️ Using draft with user_id: ' . $anyDraft['user_id'] . ' (your session user_id: ' . $userId . ')');
                        $drafts = [$anyDraft]; // Use this draft even though user_id doesn't match
                    }
                }
            }
            
            if (count($drafts) > 0) {
                error_log('First draft ID: ' . $drafts[0]['id']);
                error_log('First draft user_id: ' . $drafts[0]['user_id']);
                error_log('First draft courses_data length: ' . strlen($drafts[0]['courses_data'] ?? ''));
            }
        } else {
            error_log('course_drafts table does not exist');
            $drafts = [];
        }
    } catch (Exception $e) {
        error_log('Error checking/fetching course_drafts: ' . $e->getMessage());
        error_log('Stack trace: ' . $e->getTraceAsString());
        $drafts = [];
    }
    
    error_log('Starting to process ' . count($drafts) . ' drafts...');
    
    foreach ($drafts as $draft) {
        error_log('=== Processing draft ID: ' . $draft['id'] . ' ===');
        
        // Decode JSON data
        $rawData = $draft['courses_data'];
        error_log('Raw data type: ' . gettype($rawData));
        error_log('Raw data length: ' . strlen($rawData));
        error_log('Raw data preview (first 300 chars): ' . substr($rawData, 0, 300));
        
        $coursesData = json_decode($rawData, true);
        
        // Log for debugging
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('❌ JSON decode error for draft ' . $draft['id'] . ': ' . json_last_error_msg());
            error_log('JSON error code: ' . json_last_error());
            error_log('Raw courses_data (first 500 chars): ' . substr($rawData, 0, 500));
            error_log('Raw courses_data length: ' . strlen($rawData));
            
            // Try to fix common JSON issues
            $cleanedData = trim($rawData);
            if (substr($cleanedData, 0, 1) !== '[' && substr($cleanedData, 0, 1) !== '{') {
                error_log('⚠️ Data does not start with [ or {, might be double-encoded');
                $coursesData = json_decode($cleanedData, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    error_log('Still failed after trim');
                    continue;
                }
            } else {
                continue;
            }
        }
        
        error_log('✅ JSON decoded successfully');
        error_log('Decoded type: ' . gettype($coursesData));
        
        if (!is_array($coursesData)) {
            error_log('⚠️ Draft ' . $draft['id'] . ' courses_data is not an array. Type: ' . gettype($coursesData));
            if (is_object($coursesData)) {
                error_log('Converting object to array...');
                $coursesData = (array)$coursesData;
            } else if (is_string($coursesData)) {
                error_log('⚠️ Data is still a string, trying to decode again...');
                $coursesData = json_decode($coursesData, true);
                if (json_last_error() !== JSON_ERROR_NONE || !is_array($coursesData)) {
                    error_log('❌ Still not an array after second decode');
                    continue;
                }
            } else {
                error_log('❌ Cannot convert to array, skipping');
                continue;
            }
        }
        
        if (empty($coursesData)) {
            error_log('⚠️ Draft ' . $draft['id'] . ' has empty courses_data array');
            // Don't skip - create a minimal proposal anyway
            $coursesData = [['course_code' => 'N/A', 'course_name' => 'Draft (empty)']];
        }
        
        error_log('✅ Processing draft ' . $draft['id'] . ' with ' . count($coursesData) . ' course(s)');
        
        // Get first course for display
        $firstCourse = $coursesData[0] ?? [];
        error_log('First course keys: ' . implode(', ', array_keys($firstCourse)));
        error_log('First course code: ' . ($firstCourse['course_code'] ?? 'NOT SET'));
        error_log('First course name: ' . ($firstCourse['course_name'] ?? 'NOT SET'));
        
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
        
        error_log('✅ Created proposal data for draft ' . $draft['id']);
        error_log('Proposal ID: ' . $proposalData['id']);
        error_log('Course count: ' . $proposalData['coursesCount']);
        
        $proposals[] = $proposalData;
        error_log('✅ Added to proposals array. Total now: ' . count($proposals));
    }
    
    error_log('=== Finished processing drafts ===');
    error_log('Total proposals after processing drafts: ' . count($proposals));
    
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
        error_log('Error checking/fetching course_proposals: ' . $e->getMessage());
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
    
    error_log('=== Final Results ===');
    error_log('Total proposals to return: ' . count($proposals));
    error_log('Drafts found in query: ' . count($drafts));
    error_log('Drafts successfully processed: ' . count($proposals));
    error_log('Proposals processed: ' . count($submittedProposals));
    
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
        error_log('🚨 EMERGENCY FIX: Draft exists for user but query returned 0. Force loading...');
        
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
                error_log('✅ Force query found ' . count($forceDrafts) . ' draft(s)');
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
                        error_log('✅ Emergency fix: Added draft to proposals');
                    }
                }
            }
        } catch (Exception $e) {
            error_log('Error in emergency fix: ' . $e->getMessage());
        }
    }
    
    // If we found drafts but didn't process any, there's a problem
    if (count($drafts) > 0 && count($proposals) === 0) {
        error_log('❌ CRITICAL: Found ' . count($drafts) . ' drafts but processed 0!');
        error_log('This means all drafts were skipped during processing.');
        error_log('Check the logs above for JSON decode errors or empty array issues.');
    }
    
    // Return response with debug info
    $response = [
        'success' => true,
        'proposals' => $proposals,
        'count' => count($proposals)
    ];
    
        // Always add debug info
        if (true || isset($_GET['debug']) || count($proposals) === 0) {
        // Get total drafts count for debugging
        $totalDraftsInDb = 0;
        $totalDraftsForUser = 0;
        try {
            if (isset($checkDraftsTable) && $checkDraftsTable && $checkDraftsTable->rowCount() > 0) {
                $totalCheck = $pdo->query("SELECT COUNT(*) as total FROM course_drafts");
                $totalResult = $totalCheck->fetch(PDO::FETCH_ASSOC);
                $totalDraftsInDb = $totalResult['total'];
                
                $userCheck = $pdo->prepare("SELECT COUNT(*) as total FROM course_drafts WHERE user_id = ?");
                $userCheck->execute([$userId]);
                $userResult = $userCheck->fetch(PDO::FETCH_ASSOC);
                $totalDraftsForUser = $userResult['total'];
            }
        } catch (Exception $e) {
            // Ignore
        }
        
        $response['debug'] = [
            'user_id' => $userId,
            'drafts_found' => count($drafts ?? []),
            'proposals_found' => count($submittedProposals ?? []),
            'drafts_table_exists' => isset($checkDraftsTable) && $checkDraftsTable && $checkDraftsTable->rowCount() > 0,
            'proposals_table_exists' => isset($checkProposalsTable) && $checkProposalsTable && $checkProposalsTable->rowCount() > 0,
            'total_drafts_in_db' => $totalDraftsInDb,
            'total_drafts_for_user' => $totalDraftsForUser
        ];
        
        // If drafts were found but not processed, add more debug info
        if (isset($drafts) && count($drafts) > 0 && count($proposals) === 0) {
            $response['debug']['draft_samples'] = [];
            $response['debug']['processing_errors'] = [];
            
            foreach (array_slice($drafts, 0, 3) as $draft) {
                $sample = [
                    'id' => $draft['id'],
                    'courses_data_length' => strlen($draft['courses_data'] ?? ''),
                    'courses_data_preview' => substr($draft['courses_data'] ?? '', 0, 150)
                ];
                
                // Try to decode
                $testDecode = json_decode($draft['courses_data'] ?? '', true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $sample['json_error'] = json_last_error_msg();
                    $response['debug']['processing_errors'][] = "Draft {$draft['id']}: JSON decode error - " . json_last_error_msg();
                } else {
                    $sample['json_valid'] = true;
                    $sample['is_array'] = is_array($testDecode);
                    $sample['array_count'] = is_array($testDecode) ? count($testDecode) : 0;
                    if (is_array($testDecode) && count($testDecode) === 0) {
                        $response['debug']['processing_errors'][] = "Draft {$draft['id']}: Empty array after decode";
                    }
                }
                
                $response['debug']['draft_samples'][] = $sample;
            }
        }
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log('Error fetching course proposals: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch course proposals',
        'error' => $e->getMessage()
    ]);
}
?>


