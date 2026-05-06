<?php
// notify_librarian.php
// API endpoint to notify librarian about course compliance issues

header('Content-Type: application/json');

require_once dirname(__FILE__) . '/../includes/db_connection.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_name('ASCOM_SESSION');
    session_start();
}

try {
    // Check if user is authenticated as QA
    $isAuthenticated = false;
    
    if (isset($_SESSION['qa_logged_in']) && $_SESSION['qa_logged_in'] === true) {
        $isAuthenticated = true;
    }
    elseif (isset($_SESSION['selected_role']) && $_SESSION['selected_role']['type'] === 'quality_assurance') {
        $isAuthenticated = true;
        $_SESSION['qa_logged_in'] = true;
    }
    elseif (isset($_SESSION['user_id']) && isset($_SESSION['username'])) {
        $isAuthenticated = true;
    }
    
    if (!$isAuthenticated) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Unauthorized. Please log in as Quality Assurance.'
        ]);
        exit;
    }
    
    // Get JSON input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    // Validate required fields
    if (!isset($data['course_id']) || empty($data['course_id'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Course ID is required'
        ]);
        exit;
    }
    
    $courseId = (int)$data['course_id'];
    $dueDate = $data['due_date'] ?? null;
    $notes = $data['notes'] ?? '';
    $qaUserId = $_SESSION['user_id'] ?? null;
    
    // Get course info
    $courseStmt = $pdo->prepare("
        SELECT c.id, c.course_code, c.course_title, c.term, c.academic_year,
               p.program_name, d.department_name
        FROM courses c
        LEFT JOIN programs p ON c.program_id = p.id
        LEFT JOIN departments d ON p.department_id = d.id
        WHERE c.id = ?
    ");
    $courseStmt->execute([$courseId]);
    $course = $courseStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$course) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Course not found'
        ]);
        exit;
    }
    
    // Start transaction
    $pdo->beginTransaction();
    
    try {
        // Create notifications table if not exists
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS qa_notifications (
                id INT AUTO_INCREMENT PRIMARY KEY,
                notification_type VARCHAR(50) NOT NULL DEFAULT 'librarian_compliance',
                course_id INT NOT NULL,
                from_user_id INT NOT NULL,
                to_user_id INT NOT NULL,
                title VARCHAR(255) NOT NULL,
                message TEXT,
                due_date DATE,
                status VARCHAR(20) NOT NULL DEFAULT 'pending',
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                read_at DATETIME NULL,
                INDEX idx_course_id (course_id),
                INDEX idx_to_user (to_user_id),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        // Find librarian users
        $librarianStmt = $pdo->prepare("
            SELECT DISTINCT u.id, u.first_name, u.last_name, u.email
            FROM users u
            LEFT JOIN user_roles ur ON u.id = ur.user_id
            WHERE u.role = 'librarian' 
               OR ur.role_name = 'librarian'
               OR ur.role_type = 'librarian'
            LIMIT 1
        ");
        $librarianStmt->execute();
        $librarian = $librarianStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$librarian) {
            // Try to find any user with librarian in their role
            $fallbackStmt = $pdo->prepare("
                SELECT id, first_name, last_name, email 
                FROM users 
                WHERE role LIKE '%librarian%' 
                LIMIT 1
            ");
            $fallbackStmt->execute();
            $librarian = $fallbackStmt->fetch(PDO::FETCH_ASSOC);
        }
        
        if (!$librarian) {
            throw new Exception('No librarian user found in the system');
        }
        
        // Create notification
        $notificationTitle = 'Course Compliance Review Required';
        $notificationMessage = sprintf(
            'The course "%s" (%s) requires attention for book reference compliance. %s',
            $course['course_title'],
            $course['course_code'],
            !empty($notes) ? 'Notes: ' . $notes : ''
        );
        
        $insertStmt = $pdo->prepare("
            INSERT INTO qa_notifications (
                notification_type,
                course_id,
                from_user_id,
                to_user_id,
                title,
                message,
                due_date,
                status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')
        ");
        
        $insertStmt->execute([
            'librarian_compliance',
            $courseId,
            $qaUserId,
            $librarian['id'],
            $notificationTitle,
            $notificationMessage,
            $dueDate
        ]);
        
        $notificationId = $pdo->lastInsertId();
        
        // Commit transaction
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Librarian has been notified successfully',
            'notification_id' => $notificationId,
            'librarian' => [
                'name' => trim($librarian['first_name'] . ' ' . $librarian['last_name']),
                'email' => $librarian['email']
            ],
            'course' => [
                'code' => $course['course_code'],
                'title' => $course['course_title']
            ],
            'due_date' => $dueDate
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to notify librarian',
        'error' => $e->getMessage()
    ]);
}
?>