<?php
// test_draft_direct.php - Direct test of a single draft
header('Content-Type: application/json');

require_once dirname(__FILE__) . '/../../session_config.php';
require_once dirname(__FILE__) . '/../includes/db_connection.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$result = [
    'session' => [
        'user_id' => $_SESSION['user_id'] ?? null,
        'session_id' => session_id()
    ],
    'test' => []
];

try {
    $userId = $_SESSION['user_id'] ?? null;
    
    if (!$userId) {
        $result['error'] = 'Not authenticated';
        echo json_encode($result, JSON_PRETTY_PRINT);
        exit;
    }
    
    // Get the FIRST draft regardless of user_id (for testing)
    $stmt = $pdo->query("SELECT * FROM course_drafts ORDER BY id DESC LIMIT 1");
    $draft = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$draft) {
        $result['error'] = 'No drafts found in database';
        echo json_encode($result, JSON_PRETTY_PRINT);
        exit;
    }
    
    $result['test']['draft_found'] = true;
    $result['test']['draft_id'] = $draft['id'];
    $result['test']['draft_user_id'] = $draft['user_id'];
    $result['test']['session_user_id'] = $userId;
    $result['test']['user_id_match'] = ($draft['user_id'] == $userId);
    $result['test']['program_id'] = $draft['program_id'];
    $result['test']['term'] = $draft['term'];
    $result['test']['academic_year'] = $draft['academic_year'];
    $result['test']['year_level'] = $draft['year_level'];
    $result['test']['courses_data_length'] = strlen($draft['courses_data']);
    $result['test']['courses_data_preview'] = substr($draft['courses_data'], 0, 500);
    
    // Try to decode
    $decoded = json_decode($draft['courses_data'], true);
    $result['test']['json_decode_success'] = (json_last_error() === JSON_ERROR_NONE);
    $result['test']['json_error'] = json_last_error() !== JSON_ERROR_NONE ? json_last_error_msg() : null;
    $result['test']['decoded_type'] = gettype($decoded);
    $result['test']['decoded_is_array'] = is_array($decoded);
    
    if (is_array($decoded)) {
        $result['test']['decoded_count'] = count($decoded);
        if (count($decoded) > 0) {
            $result['test']['first_course'] = $decoded[0];
        }
    }
    
    // Now test the actual query used by the API
    $apiQuery = $pdo->prepare("
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
        LIMIT 10
    ");
    
    $apiQuery->execute([$userId]);
    $apiResults = $apiQuery->fetchAll(PDO::FETCH_ASSOC);
    
    $result['test']['api_query_results'] = count($apiResults);
    $result['test']['api_query_matches'] = count($apiResults) > 0;
    
} catch (Exception $e) {
    $result['error'] = $e->getMessage();
    $result['trace'] = $e->getTraceAsString();
}

echo json_encode($result, JSON_PRETTY_PRINT);
?>

