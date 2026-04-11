<?php
// submit_book_request.php
// API endpoint to handle book request submissions from teachers

require_once dirname(__FILE__) . '/../../includes/db_connection.php';
session_start();

header('Content-Type: application/json');

// Check if user is logged in as teacher
if (!isset($_SESSION['teacher_logged_in']) || $_SESSION['teacher_logged_in'] !== true) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit();
}

try {
    // Get form data
    $book_title = isset($_POST['book_title']) ? trim($_POST['book_title']) : '';
    $author = isset($_POST['author']) ? trim($_POST['author']) : '';
    $edition = isset($_POST['edition']) ? trim($_POST['edition']) : '';
    $publication_year = isset($_POST['publication_year']) ? trim($_POST['publication_year']) : '';
    $publisher = isset($_POST['publisher']) ? trim($_POST['publisher']) : '';
    $material_type = isset($_POST['material_type']) ? trim($_POST['material_type']) : '';
    $course_code_title = isset($_POST['course_code_title']) ? trim($_POST['course_code_title']) : '';
    $justification = isset($_POST['justification']) ? trim($_POST['justification']) : '';
    
    // Validate required fields
    if (empty($book_title) || empty($author) || empty($edition) || empty($publication_year) || 
        empty($publisher) || empty($material_type) || empty($course_code_title) || 
        empty($justification)) {
        echo json_encode(['success' => false, 'error' => 'All required fields must be filled']);
        exit();
    }
    
    // Get user ID from session
    $user_id = $_SESSION['user_id'] ?? null;
    if (!$user_id) {
        echo json_encode(['success' => false, 'error' => 'User session not found']);
        exit();
    }
    
    // Handle file upload if provided
    $supporting_file_path = null;
    $supporting_file_name = null;
    
    if (isset($_FILES['supporting_file']) && $_FILES['supporting_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = dirname(__FILE__) . '/../../uploads/book_requests/';
        
        // Create upload directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file = $_FILES['supporting_file'];
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $allowed_extensions = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
        
        if (!in_array(strtolower($file_extension), $allowed_extensions)) {
            echo json_encode(['success' => false, 'error' => 'Invalid file type. Allowed: PDF, JPG, PNG, DOC, DOCX']);
            exit();
        }
        
        // Generate unique filename
        $file_name = 'book_request_' . $user_id . '_' . time() . '.' . $file_extension;
        $file_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            $supporting_file_path = 'uploads/book_requests/' . $file_name;
            $supporting_file_name = $file['name'];
        }
    }
    
    // Parse course code and title
    $course_parts = explode(' - ', $course_code_title, 2);
    $course_code = trim($course_parts[0]);
    $course_name = isset($course_parts[1]) ? trim($course_parts[1]) : '';
    
    // Insert request into database
    // Note: Adjust table name and columns based on your database schema
    $stmt = $pdo->prepare("
        INSERT INTO book_requests (
            user_id,
            requester_name,
            requester_role,
            book_title,
            author_first,
            author_last,
            publication_year,
            edition,
            publisher,
            material_type,
            course_code,
            course_name,
            justification,
            supporting_file_path,
            supporting_file_name,
            status,
            priority,
            created_at
        ) VALUES (
            :user_id,
            :requester_name,
            'FACULTY',
            :book_title,
            :author_first,
            :author_last,
            :publication_year,
            :edition,
            :publisher,
            :material_type,
            :course_code,
            :course_name,
            :justification,
            :supporting_file_path,
            :supporting_file_name,
            'PENDING',
            'MEDIUM',
            NOW()
        )
    ");
    
    // Parse author name (assuming format: "First Last" or "Last, First")
    $author_first = '';
    $author_last = '';
    
    if (strpos($author, ',') !== false) {
        // Format: "Last, First"
        $author_parts = explode(',', $author, 2);
        $author_last = trim($author_parts[0]);
        $author_first = trim($author_parts[1]);
    } else {
        // Format: "First Last"
        $author_parts = explode(' ', $author);
        $author_first = $author_parts[0];
        $author_last = isset($author_parts[1]) ? $author_parts[1] : '';
    }
    
    // Edition and publication year are now separate fields
    // No parsing needed
    
    // Get requester name from session
    $requester_name = '';
    if (isset($_SESSION['user_title']) && isset($_SESSION['user_first_name']) && isset($_SESSION['user_last_name'])) {
        $title = $_SESSION['user_title'] ? $_SESSION['user_title'] . ' ' : '';
        $requester_name = $title . $_SESSION['user_first_name'] . ' ' . $_SESSION['user_last_name'];
    } else {
        $requester_name = $_SESSION['username'] ?? 'Unknown';
    }
    
    $stmt->execute([
        ':user_id' => $user_id,
        ':requester_name' => $requester_name,
        ':book_title' => $book_title,
        ':author_first' => $author_first,
        ':author_last' => $author_last,
        ':publication_year' => $publication_year,
        ':edition' => $edition,
        ':publisher' => $publisher,
        ':material_type' => $material_type,
        ':course_code' => $course_code,
        ':course_name' => $course_name,
        ':justification' => $justification,
        ':supporting_file_path' => $supporting_file_path,
        ':supporting_file_name' => $supporting_file_name
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Book request submitted successfully'
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error occurred. Please try again later.'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred. Please try again later.'
    ]);
}
?>

