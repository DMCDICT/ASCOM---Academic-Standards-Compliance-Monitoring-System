<?php
// process_add_book_reference.php
// Handles adding book references with multiple input methods

header('Content-Type: application/json');
require_once dirname(__FILE__) . '/../session_config.php';
require_once dirname(__FILE__) . '/includes/db_connection.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$response = ['success' => false, 'message' => '', 'data' => null];

// Book reference creation is now handled exclusively by the Librarian module.
// Even if the dean is authenticated, this endpoint no longer allows writes.
$response['message'] = 'Book reference creation and updates are now handled by the Librarian module. Please coordinate with the librarian for changes.';
echo json_encode($response);
exit;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $inputMethod = $_POST['input_method'] ?? 'manual';
        $courseId = $_POST['course_id'] ?? null;
        
        if (!$courseId) {
            $response['message'] = 'Course ID is required';
            echo json_encode($response);
            exit;
        }
        
        // Verify course exists and user has access
        $courseQuery = "SELECT c.*, d.department_code 
                       FROM courses c 
                       JOIN programs p ON c.program_id = p.id 
                       JOIN departments d ON p.department_id = d.id 
                       WHERE c.id = ?";
        $courseStmt = $pdo->prepare($courseQuery);
        $courseStmt->execute([$courseId]);
        $course = $courseStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$course) {
            $response['message'] = 'Course not found';
            echo json_encode($response);
            exit;
        }
        
        // Check if user has access to this course's department
        $userDepartmentCode = $_SESSION['selected_role']['department_code'] ?? null;
        if ($userDepartmentCode !== $course['department_code']) {
            $response['message'] = 'Access denied to this course';
            echo json_encode($response);
            exit;
        }
        
        $createdBy = $_SESSION['user_id'] ?? null;
        $addedBooks = [];
        
        switch ($inputMethod) {
            case 'manual':
                $addedBooks = processManualInput($_POST, $courseId, $createdBy);
                break;
                
            case 'batch':
                $addedBooks = processBatchInput($_POST, $courseId, $createdBy);
                break;
                
            case 'library':
                $addedBooks = processLibrarySelection($_POST, $courseId, $createdBy);
                break;
                
            case 'pdf':
                $addedBooks = processPDFImport($_POST, $courseId, $createdBy);
                break;
                
            case 'auto':
                $addedBooks = processAutoGeneration($_POST, $courseId, $createdBy);
                break;
                
            default:
                $response['message'] = 'Invalid input method';
                echo json_encode($response);
                exit;
        }
        
        if (empty($addedBooks)) {
            $response['message'] = 'No books were added';
            echo json_encode($response);
            exit;
        }
        
        $response['success'] = true;
        $response['message'] = count($addedBooks) . ' book reference(s) added successfully';
        $response['data'] = $addedBooks;
        
    } catch (Exception $e) {
        $response['message'] = 'Failed to add book reference: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);

// Process manual input
function processManualInput($data, $courseId, $createdBy) {
    global $pdo;
    
    $bookData = [
        'course_id' => $courseId,
        'book_title' => trim($data['book_title'] ?? ''),
        'isbn' => trim($data['isbn'] ?? ''),
        'publisher' => trim($data['publisher'] ?? ''),
        'publication_year' => $data['copyright_year'] ?? null,
        'edition' => trim($data['edition'] ?? ''),
        'location' => trim($data['location'] ?? ''),
        'call_number' => trim($data['call_number'] ?? ''),
        'author' => trim($data['authors'] ?? ''), // Map authors to author field
        'created_by' => $createdBy,
        'requested_by' => $data['requested_by'] ?? null
    ];
    
    // Validate required fields (author and call_number are optional)
    if (empty($bookData['book_title'])) {
        throw new Exception('Book title is required');
    }
    
    // Insert book reference
    $insertQuery = "INSERT INTO book_references 
                   (course_id, book_title, isbn, publisher, publication_year, edition, location, call_number, author, created_by, requested_by, no_of_copies) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL)";
    
    $stmt = $pdo->prepare($insertQuery);
    $stmt->execute([
        $bookData['course_id'],
        $bookData['book_title'],
        $bookData['isbn'],
        $bookData['publisher'],
        $bookData['publication_year'],
        $bookData['edition'],
        $bookData['location'],
        $bookData['call_number'],
        $bookData['author'],
        $bookData['created_by'],
        $bookData['requested_by']
    ]);
    
    return [['id' => $pdo->lastInsertId(), 'title' => $bookData['book_title']]];
}

// Process library selection
function processLibrarySelection($data, $courseId, $createdBy) {
    global $pdo;
    
    $selectedBookIds = $data['selected_library_books'] ?? [];
    if (empty($selectedBookIds)) {
        throw new Exception('No books selected from library');
    }
    
    $addedBooks = [];
    
    foreach ($selectedBookIds as $bookId) {
        // Get book details from library_books table
        $libraryQuery = "SELECT * FROM library_books WHERE id = ?";
        $libraryStmt = $pdo->prepare($libraryQuery);
        $libraryStmt->execute([$bookId]);
        $libraryBook = $libraryStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$libraryBook) {
            continue; // Skip if book not found
        }
        
        // Insert as book reference
        $insertQuery = "INSERT INTO book_references 
                       (course_id, book_title, isbn, publisher, publication_year, edition, location, call_number, created_by, requested_by, no_of_copies) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL)";
        
        $stmt = $pdo->prepare($insertQuery);
        $stmt->execute([
            $courseId,
            $libraryBook['title'],
            $libraryBook['isbn'],
            $libraryBook['publisher'],
            $libraryBook['copyright_year'],
            $libraryBook['edition'],
            $libraryBook['location'],
            $libraryBook['call_number'],
            $createdBy,
            $data['requested_by'] ?? null
        ]);
        
        $addedBooks[] = [
            'id' => $pdo->lastInsertId(),
            'title' => $libraryBook['title'],
            'source' => 'library'
        ];
    }
    
    return $addedBooks;
}

// Process PDF import
function processPDFImport($data, $courseId, $createdBy) {
    // TODO: Integrate with PDF processing libraries
    return [];
}

// Process auto-generation
function processAutoGeneration($data, $courseId, $createdBy) {
    global $pdo;
    
    // Get course information for context
    $courseQuery = "SELECT c.*, p.program_name, p.major 
                   FROM courses c 
                   JOIN programs p ON c.program_id = p.id 
                   WHERE c.id = ?";
    $courseStmt = $pdo->prepare($courseQuery);
    $courseStmt->execute([$courseId]);
    $course = $courseStmt->fetch(PDO::FETCH_ASSOC);
    
    // Search library for relevant books based on course content
    $searchTerms = [
        $course['course_name'],
        $course['program_name'],
        $course['major']
    ];
    
    $searchQuery = "SELECT * FROM library_books 
                   WHERE MATCH(title, authors, description, keywords) AGAINST (? IN NATURAL LANGUAGE MODE)
                   OR title LIKE ? OR authors LIKE ? OR subject_category LIKE ?
                   LIMIT 5";
    
    $searchStmt = $pdo->prepare($searchQuery);
    $searchTerm = implode(' ', $searchTerms);
    $likeTerm = '%' . $searchTerm . '%';
    $searchStmt->execute([$searchTerm, $likeTerm, $likeTerm, $likeTerm]);
    $suggestedBooks = $searchStmt->fetchAll(PDO::FETCH_ASSOC);
    
    $addedBooks = [];
    
    foreach ($suggestedBooks as $book) {
        // Check if already exists for this course
        $existsQuery = "SELECT id FROM book_references WHERE course_id = ? AND book_title = ?";
        $existsStmt = $pdo->prepare($existsQuery);
        $existsStmt->execute([$courseId, $book['title']]);
        
        if ($existsStmt->fetch()) {
            continue; // Skip if already exists
        }
        
        // Insert as book reference
        $insertQuery = "INSERT INTO book_references 
                       (course_id, book_title, isbn, publisher, publication_year, edition, location, call_number, created_by, requested_by, no_of_copies) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL)";
        
        $stmt = $pdo->prepare($insertQuery);
        $stmt->execute([
            $courseId,
            $book['title'],
            $book['isbn'],
            $book['publisher'],
            $book['copyright_year'],
            $book['edition'],
            $book['location'],
            $book['call_number'],
            $createdBy,
            $data['requested_by'] ?? null
        ]);
        
        $addedBooks[] = [
            'id' => $pdo->lastInsertId(),
            'title' => $book['title'],
            'source' => 'auto_generated'
        ];
    }
    
    return $addedBooks;
}

// Process batch input
function processBatchInput($data, $courseId, $createdBy) {
    global $pdo;
    
    $booksJson = $data['books'] ?? '[]';
    $books = json_decode($booksJson, true);
    
    if (!is_array($books) || empty($books)) {
        throw new Exception('No books provided in batch');
    }
    
    $addedBooks = [];
    
    foreach ($books as $bookData) {
        $book = [
            'course_id' => $courseId,
            'book_title' => trim($bookData['book_title'] ?? ''),
            'isbn' => trim($bookData['isbn'] ?? ''),
            'publisher' => trim($bookData['publisher'] ?? ''),
            'publication_year' => $bookData['publication_year'] ?? null,
            'edition' => trim($bookData['edition'] ?? ''),
            'location' => '',
            'call_number' => trim($bookData['call_number'] ?? ''),
            'author' => trim($bookData['authors'] ?? ''),
            'created_by' => $createdBy,
            'requested_by' => null
        ];
        
        // Validate required fields
        if (empty($book['book_title'])) {
            continue; // Skip invalid books
        }
        
        // Insert book reference
        $insertQuery = "INSERT INTO book_references 
                       (course_id, book_title, isbn, publisher, publication_year, edition, location, call_number, author, created_by, requested_by, no_of_copies) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL)";
        
        $stmt = $pdo->prepare($insertQuery);
        $stmt->execute([
            $book['course_id'],
            $book['book_title'],
            $book['isbn'],
            $book['publisher'],
            $book['publication_year'],
            $book['edition'],
            $book['location'],
            $book['call_number'],
            $book['author'],
            $book['created_by'],
            $book['requested_by']
        ]);
        
        $addedBooks[] = ['id' => $pdo->lastInsertId(), 'title' => $book['book_title']];
    }
    
    return $addedBooks;
}
?>
