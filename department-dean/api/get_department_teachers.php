<?php
// get_department_teachers.php
// Get teachers from a specific department for the Dean to assign

require_once '../includes/db_connection.php';

// Support both department_id and dept_code
$department_id = null;

if (isset($_GET['department_id'])) {
    $department_id = (int)$_GET['department_id'];
} elseif (isset($_GET['dept_code'])) {
    // Get department ID from department code
    $deptCode = $_GET['dept_code'];
    $deptQuery = "SELECT id FROM departments WHERE department_code = ?";
    $deptStmt = $pdo->prepare($deptQuery);
    $deptStmt->execute([$deptCode]);
    $deptRow = $deptStmt->fetch(PDO::FETCH_ASSOC);
    $department_id = $deptRow['id'] ?? null;
}

header('Content-Type: application/json');

if (!$department_id || $department_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid department']);
    exit;
}

try {
    // Get all faculty/teachers from this department.
    // Role data differs across deployments:
    // - users.role_id (primary role via roles table)
    // - users.role (legacy string role)
    // - user_roles table (additional roles; sometimes used for teacher/faculty too)
    //
    // Treat "teacher" and "faculty" as synonyms for assignment UIs.
    $joins = [];
    $roleConditions = [];

    if (ascom_table_has_column($pdo, 'users', 'role')) {
        $roleConditions[] = "u.role IN ('teacher', 'faculty')";
    }

    if (ascom_table_has_column($pdo, 'users', 'role_id') && ascom_table_has_column($pdo, 'roles', 'role_name')) {
        $joins[] = "LEFT JOIN roles br ON u.role_id = br.id";
        $roleConditions[] = "br.role_name IN ('teacher', 'faculty')";
    }

    if (ascom_table_has_column($pdo, 'user_roles', 'user_id')) {
        $activePredicate = ascom_user_roles_active_predicate($pdo, 'ur');
        $teacherRolePredicate = ascom_user_roles_role_predicate($pdo, 'ur', 'teacher');
        $facultyRolePredicate = ascom_user_roles_role_predicate($pdo, 'ur', 'faculty');

        $joins[] = "
            LEFT JOIN user_roles ur
              ON u.id = ur.user_id
             AND {$activePredicate}
        ";
        $roleConditions[] = "({$teacherRolePredicate} OR {$facultyRolePredicate})";
    }

    if (count($roleConditions) === 0) {
        // Last resort: do not filter by role if we can't determine role columns.
        $roleConditions[] = '1=1';
    }

    $selectFields = [
        'u.id',
        'u.employee_no',
        'u.first_name',
        'u.last_name',
        'u.title',
    ];

    // Prefer institutional_email/mobile_no fields used by dean UI; fall back to email when needed.
    if (ascom_table_has_column($pdo, 'users', 'institutional_email')) {
        $selectFields[] = 'u.institutional_email';
    } elseif (ascom_table_has_column($pdo, 'users', 'email')) {
        $selectFields[] = 'u.email AS institutional_email';
    }

    if (ascom_table_has_column($pdo, 'users', 'mobile_no')) {
        $selectFields[] = 'u.mobile_no';
    }

    $query = "
        SELECT DISTINCT " . implode(', ', $selectFields) . "
        FROM users u
        " . implode("\n", $joins) . "
        WHERE u.department_id = ?
          AND u.is_active = 1
          AND (" . implode(' OR ', $roleConditions) . ")
        ORDER BY u.first_name ASC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$department_id]);
    $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Also check if any are already program heads
    $headStmt = $pdo->query("
        SELECT teacher_id, program_id 
        FROM program_heads 
        WHERE is_active = TRUE
    ");
    $heads = $headStmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    // Mark teachers who are program heads
    foreach ($teachers as &$teacher) {
        if (isset($heads[$teacher['id']])) {
            $teacher['is_program_head'] = true;
            $teacher['head_program_id'] = $heads[$teacher['id']];
        } else {
            $teacher['is_program_head'] = false;
        }
    }
    
    echo json_encode([
        'success' => true, 
        'teachers' => $teachers
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
