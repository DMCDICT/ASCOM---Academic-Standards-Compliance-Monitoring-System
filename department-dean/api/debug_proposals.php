<?php
// debug_proposals.php - Direct debugging of the proposals API
header('Content-Type: application/json');

require_once dirname(__FILE__) . '/../../session_config.php';
require_once dirname(__FILE__) . '/../includes/db_connection.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$debug = [
    'session' => [
        'user_id' => $_SESSION['user_id'] ?? 'NOT SET',
        'session_id' => session_id(),
        'session_status' => session_status()
    ],
    'tables' => [],
    'drafts' => [],
    'errors' => []
];

try {
    $userId = $_SESSION['user_id'] ?? null;
    
    if (!$userId) {
        $debug['errors'][] = 'User not authenticated';
        echo json_encode($debug, JSON_PRETTY_PRINT);
        exit;
    }
    
    // Check tables
    $tables = ['course_drafts', 'course_proposals'];
    foreach ($tables as $table) {
        try {
            $check = $pdo->query("SHOW TABLES LIKE '$table'");
            $exists = $check->rowCount() > 0;
            $debug['tables'][$table] = $exists;
            
            if ($exists) {
                // Count records
                $countStmt = $pdo->prepare("SELECT COUNT(*) as count FROM $table WHERE user_id = ?");
                $countStmt->execute([$userId]);
                $count = $countStmt->fetch(PDO::FETCH_ASSOC);
                $debug['tables'][$table . '_count'] = $count['count'];
                
                // Get sample data
                if ($table === 'course_drafts') {
                    $sampleStmt = $pdo->prepare("
                        SELECT id, program_id, term, academic_year, year_level, 
                               LENGTH(courses_data) as data_len,
                               LEFT(courses_data, 200) as data_preview,
                               created_at
                        FROM $table 
                        WHERE user_id = ? 
                        LIMIT 3
                    ");
                    $sampleStmt->execute([$userId]);
                    $samples = $sampleStmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    foreach ($samples as $sample) {
                        // Try to decode
                        $fullStmt = $pdo->prepare("SELECT courses_data FROM $table WHERE id = ?");
                        $fullStmt->execute([$sample['id']]);
                        $full = $fullStmt->fetch(PDO::FETCH_ASSOC);
                        $decoded = json_decode($full['courses_data'], true);
                        
                        $debug['drafts'][] = [
                            'id' => $sample['id'],
                            'program_id' => $sample['program_id'],
                            'term' => $sample['term'],
                            'academic_year' => $sample['academic_year'],
                            'year_level' => $sample['year_level'],
                            'data_length' => $sample['data_len'],
                            'data_preview' => $sample['data_preview'],
                            'json_valid' => json_last_error() === JSON_ERROR_NONE,
                            'json_error' => json_last_error() !== JSON_ERROR_NONE ? json_last_error_msg() : null,
                            'decoded_type' => gettype($decoded),
                            'decoded_is_array' => is_array($decoded),
                            'decoded_count' => is_array($decoded) ? count($decoded) : 0,
                            'first_course_code' => is_array($decoded) && isset($decoded[0]) ? ($decoded[0]['course_code'] ?? 'N/A') : 'N/A',
                            'created_at' => $sample['created_at']
                        ];
                    }
                }
            }
        } catch (Exception $e) {
            $debug['errors'][] = "Error checking table $table: " . $e->getMessage();
        }
    }
    
    // Test the actual API call
    $limit = 10;
    $testProposals = [];
    
    if ($debug['tables']['course_drafts'] ?? false) {
        try {
            $draftsQuery = "
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
                WHERE cd.user_id = ?
                ORDER BY cd.updated_at DESC
                LIMIT ?
            ";
            
            $draftsStmt = $pdo->prepare($draftsQuery);
            $draftsStmt->execute([$userId, $limit]);
            $drafts = $draftsStmt->fetchAll(PDO::FETCH_ASSOC);
            
            $debug['query_result'] = [
                'drafts_found' => count($drafts),
                'processed' => 0,
                'skipped' => 0
            ];
            
            foreach ($drafts as $draft) {
                $coursesData = json_decode($draft['courses_data'], true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $debug['query_result']['skipped']++;
                    $debug['query_result']['skip_reason'] = 'JSON decode error: ' . json_last_error_msg();
                    continue;
                }
                
                if (!is_array($coursesData) || empty($coursesData)) {
                    $debug['query_result']['skipped']++;
                    $debug['query_result']['skip_reason'] = 'Not array or empty';
                    continue;
                }
                
                $debug['query_result']['processed']++;
                $testProposals[] = [
                    'id' => 'draft_' . $draft['id'],
                    'status' => 'Draft',
                    'courses_count' => count($coursesData)
                ];
            }
        } catch (Exception $e) {
            $debug['errors'][] = 'Error fetching drafts: ' . $e->getMessage();
        }
    }
    
    $debug['test_proposals'] = $testProposals;
    
} catch (Exception $e) {
    $debug['errors'][] = 'Fatal error: ' . $e->getMessage();
}

echo json_encode($debug, JSON_PRETTY_PRINT);
?>

