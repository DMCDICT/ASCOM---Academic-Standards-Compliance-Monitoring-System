<?php
// force_load_draft.php - Force load the draft regardless of user_id
header('Content-Type: application/json');

require_once dirname(__FILE__) . '/../../session_config.php';
require_once dirname(__FILE__) . '/../includes/db_connection.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$userId = $_SESSION['user_id'] ?? null;

// Get the draft directly without user_id filter
$stmt = $pdo->query("
    SELECT 
        cd.id,
        cd.user_id,
        cd.program_id,
        cd.term,
        cd.academic_year,
        cd.year_level,
        cd.courses_data,
        cd.created_at,
        cd.updated_at
    FROM course_drafts cd
    ORDER BY cd.updated_at DESC
    LIMIT 1
");

$draft = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$draft) {
    echo json_encode([
        'success' => false,
        'message' => 'No draft found in database'
    ]);
    exit;
}

// Get program info
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

// Decode JSON
$coursesData = json_decode($draft['courses_data'], true);

if (json_last_error() !== JSON_ERROR_NONE || !is_array($coursesData) || empty($coursesData)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON in courses_data',
        'json_error' => json_last_error_msg(),
        'draft_id' => $draft['id']
    ]);
    exit;
}

// Build proposal
$firstCourse = $coursesData[0] ?? [];
$totalAttachments = 0;
$totalReferences = 0;
foreach ($coursesData as $course) {
    $totalAttachments += count($course['attachments'] ?? []);
    $totalReferences += count($course['learning_materials'] ?? []);
}

$proposal = [
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

echo json_encode([
    'success' => true,
    'proposals' => [$proposal],
    'count' => 1,
    'debug' => [
        'draft_user_id' => $draft['user_id'],
        'session_user_id' => $userId,
        'match' => ($draft['user_id'] == $userId)
    ]
]);
?>

