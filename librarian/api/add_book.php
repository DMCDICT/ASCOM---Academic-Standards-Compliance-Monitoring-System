<?php
// Suppress warnings and errors that might output HTML before JSON
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Start output buffering to catch any accidental output
ob_start();

// Set error handler to catch fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== NULL && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Server error occurred: ' . $error['message'],
            'file' => $error['file'],
            'line' => $error['line']
        ]);
        exit;
    }
});

// Include session configuration
require_once dirname(__FILE__) . '/../../session_config.php';
session_start();

// Set JSON header early
header('Content-Type: application/json');

// Include database connection with error handling
try {
    // Get current buffer content before requiring connection file
    $bufferBefore = ob_get_contents();
    
    require_once dirname(__FILE__) . '/../../super_admin-mis/includes/db_connection.php';
    
    // Check if buffer content changed (meaning output was generated, likely from die())
    $bufferAfter = ob_get_contents();
    if ($bufferAfter !== $bufferBefore && !empty($bufferAfter)) {
        error_log("Database connection output detected: " . substr($bufferAfter, 0, 500));
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }
    
    // Check if connection exists and is valid
    if (!isset($conn)) {
        throw new Exception('Database connection variable not set');
    }
    
    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }
} catch (Exception $e) {
    ob_clean();
    error_log("Database connection error in add_book.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
} catch (Throwable $e) {
    ob_clean();
    error_log("Database connection fatal error in add_book.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Clear any output that might have been generated
ob_clean();

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get JSON input
$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);

// Check if JSON decode failed
if ($input === null && json_last_error() !== JSON_ERROR_NONE) {
    ob_clean();
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid JSON input: ' . json_last_error_msg(),
        'raw_input_preview' => substr($rawInput, 0, 200) // First 200 chars for debugging
    ]);
    exit;
}

// Check if input is empty
if (empty($input)) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'No input data provided']);
    exit;
}

// Get librarian user ID from session
// Check various possible session variable names for librarian user ID
$createdBy = $_SESSION['user_id'] ?? $_SESSION['librarian_id'] ?? $_SESSION['id'] ?? null;

// If createdBy is null, set to 0 or handle appropriately
// Note: Some databases require a value, so we'll use 0 as default
if ($createdBy === null) {
    error_log("Warning: createdBy is null in add_book.php. Session vars: user_id=" . ($_SESSION['user_id'] ?? 'not set') . ", librarian_id=" . ($_SESSION['librarian_id'] ?? 'not set') . ", id=" . ($_SESSION['id'] ?? 'not set'));
    $createdBy = 0; // Default to 0 if not set
}

// Check if this is a batch submission with courses
$isBatchWithCourses = isset($input['input_method']) && $input['input_method'] === 'batch_with_courses';

if ($isBatchWithCourses) {
    // Handle batch submission with courses - check for duplicates
    if (empty($input['courses']) || !is_array($input['courses'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid courses data']);
        exit;
    }
    
    try {
        $totalBooksImported = 0;
        $totalSkippedDuplicates = 0;
        $errors = [];
        
        foreach ($input['courses'] as $courseData) {
            $courseCode = trim($courseData['course_code'] ?? '');
            $courseTitle = trim($courseData['course_title'] ?? '');
            $programCode = trim($courseData['program_code'] ?? '');
            $existingCourseId = $courseData['course_id'] ?? null;
            $books = $courseData['books'] ?? [];
            
            if (empty($courseCode) || empty($courseTitle) || empty($programCode)) {
                $errors[] = "Invalid course data: missing code, title, or program";
                continue;
            }
            
            // Get or create course
            $courseId = $existingCourseId;
            if (!$courseId) {
                // Get program ID
                $getProgram = $conn->prepare("SELECT id FROM programs WHERE program_code = ? LIMIT 1");
                $getProgram->bind_param("s", $programCode);
                $getProgram->execute();
                $programResult = $getProgram->get_result();
                if ($programRow = $programResult->fetch_assoc()) {
                    $programId = $programRow['id'];
                } else {
                    $errors[] = "Program not found: {$programCode}";
                    $getProgram->close();
                    continue;
                }
                $getProgram->close();
                
                // Check if course exists
                $checkCourse = $conn->prepare("SELECT id FROM courses WHERE course_code = ? AND program_id = ? LIMIT 1");
                $checkCourse->bind_param("si", $courseCode, $programId);
                $checkCourse->execute();
                $result = $checkCourse->get_result();
                if ($row = $result->fetch_assoc()) {
                    $courseId = $row['id'];
                } else {
                    // Create course
                    $currentDate = new DateTime();
                    $currentYear = $currentDate->format('Y');
                    $month = (int)$currentDate->format('m');
                    
                    if ($month >= 8) {
                        $schoolYear = $currentYear . '-' . ($currentYear + 1);
                        $schoolTerm = '1st Semester';
                    } else if ($month >= 1 && $month < 5) {
                        $schoolYear = ($currentYear - 1) . '-' . $currentYear;
                        $schoolTerm = '2nd Semester';
                    } else {
                        $schoolYear = ($currentYear - 1) . '-' . $currentYear;
                        $schoolTerm = 'Summer';
                    }
                    
                    $createCourse = $conn->prepare("INSERT INTO courses (course_code, course_title, program_id, academic_year, term) VALUES (?, ?, ?, ?, ?)");
                    $createCourse->bind_param("ssiss", $courseCode, $courseTitle, $programId, $schoolYear, $schoolTerm);
                    if ($createCourse->execute()) {
                        $courseId = $conn->insert_id;
                    } else {
                        $errors[] = "Failed to create course: {$courseCode} - " . $createCourse->error;
                        continue;
                    }
                    $createCourse->close();
                }
                $checkCourse->close();
            }
            
            if (!$courseId) {
                $errors[] = "Could not get or create course: {$courseCode}";
                continue;
            }
            
            // Process books for this course - check for duplicates
            if (!empty($books) && is_array($books)) {
                $sql = "INSERT INTO book_references (
                    course_id,
                    call_number,
                    isbn,
                    book_title,
                    no_of_copies,
                    publication_year,
                    edition,
                    author,
                    publisher,
                    location,
                    processing_status,
                    created_by,
                    created_at
                ) SELECT ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()
                WHERE NOT EXISTS (
                    SELECT 1 FROM book_references 
                    WHERE course_id = ? 
                    AND LOWER(TRIM(book_title)) = LOWER(TRIM(?))
                    AND (LOWER(TRIM(author)) = LOWER(TRIM(?)) OR (author IS NULL AND ? IS NULL))
                )";
                
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    $errors[] = "Prepare failed for course {$courseCode}: " . $conn->error;
                    continue;
                }
                
                foreach ($books as $book) {
                    $callNumber = trim($book['call_number'] ?? '');
                    $isbn = trim($book['isbn'] ?? '');
                    $bookTitle = trim($book['book_title'] ?? '');
                    $noOfCopies = is_numeric($book['no_of_copies'] ?? 1) ? intval($book['no_of_copies']) : 1;
                    $publicationYear = trim($book['publication_year'] ?? '');
                    $edition = trim($book['edition'] ?? '');
                    $authors = trim($book['authors'] ?? '');
                    $publisher = trim($book['publisher'] ?? '');
                    $location = ''; // Location not required for import
                    
                    if (empty($bookTitle)) {
                        continue; // Skip books without title
                    }
                    
                    $processingStatus = 'processing';
                    if (!empty($callNumber) && !empty($noOfCopies) && !empty($location)) {
                        $processingStatus = 'completed';
                    }
                    
                    $stmt->bind_param("isssissssssissis",
                        $courseId, $callNumber, $isbn, $bookTitle, $noOfCopies,
                        $publicationYear, $edition, $authors, $publisher, $location,
                        $processingStatus, $createdBy,
                        $courseId, $bookTitle, $authors, $authors
                    );
                    
                    if ($stmt->execute()) {
                        if ($conn->affected_rows > 0) {
                            $totalBooksImported++;
                        } else {
                            $totalSkippedDuplicates++;
                        }
                    } else {
                        $errors[] = "Failed to process book: {$bookTitle} - " . $stmt->error;
                    }
                }
                $stmt->close();
            }
        }
        
        echo json_encode([
            'success' => true,
            'message' => "Import completed",
            'total_books_imported' => $totalBooksImported,
            'skipped_duplicates' => $totalSkippedDuplicates,
            'errors' => $errors
        ]);
        
    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error occurred: ' . $e->getMessage()]);
    }
} else if (isset($input['input_method']) && $input['input_method'] === 'batch') {
    // Handle batch submission
    if (empty($input['books']) || !is_array($input['books'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid batch data']);
        exit;
    }
    
    if (empty($input['course_id'])) {
        echo json_encode(['success' => false, 'message' => 'Course ID is required']);
        exit;
    }
    
    try {
        $courseId = $input['course_id'];
        $insertedCount = 0;
        $errors = [];
        
                // Prepare SQL statement
        $sql = "INSERT INTO book_references (
            course_id,
            call_number,
            isbn,
            book_title,
            no_of_copies,
            publication_year,
            edition,
            author,
            publisher,
            location,
            processing_status,
            created_by,
            created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        foreach ($input['books'] as $book) {
            $callNumber = trim($book['call_number'] ?? '');
            $isbn = $book['isbn'] ?? '';
            $bookTitle = $book['book_title'] ?? '';
            $noOfCopies = trim($book['no_of_copies'] ?? '');
            $copyright = $book['copyright'] ?? '';
            $edition = $book['edition'] ?? '';
            $authors = $book['authors'] ?? '';
            $publisher = $book['publisher'] ?? '';
            $location = trim($book['location'] ?? '');

            // Validate required fields
            if (empty($bookTitle) || empty($copyright) || empty($location)) {   
                $errors[] = "Book missing title, copyright, or location: " . ($bookTitle ?: 'Untitled');
                continue;
            }

            // Determine processing_status based on whether all required cataloging fields are filled
            // If call_number, no_of_copies, and location are all provided, set status to 'completed'
            $processingStatus = 'processing'; // Default status
            if (!empty($callNumber) && !empty($noOfCopies) && !empty($location)) {
                $processingStatus = 'completed';
            }

            // Convert no_of_copies to integer if it's a string
            $noOfCopiesInt = is_numeric($noOfCopies) ? intval($noOfCopies) : 1;

            // Bind parameters
            $stmt->bind_param("isssissssssi",
                $courseId,
                $callNumber,
                $isbn,
                $bookTitle,
                $noOfCopiesInt,
                $copyright,
                $edition,
                $authors,
                $publisher,
                $location,
                $processingStatus,  // processing_status: 'completed' if all fields filled, else 'processing'
                $createdBy
            );
            
            if ($stmt->execute()) {
                $insertedCount++;
            } else {
                $errors[] = "Failed to add book: " . $bookTitle . " - " . $stmt->error;
            }
        }
        
        $stmt->close();
        
        if ($insertedCount > 0) {
            $message = "Successfully added {$insertedCount} book" . ($insertedCount > 1 ? 's' : '');
            if (!empty($errors)) {
                $message .= ". Some errors occurred: " . implode(', ', $errors);
            }
            echo json_encode([
                'success' => true,
                'message' => $message,
                'inserted_count' => $insertedCount,
                'errors' => $errors
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to add any books. Errors: ' . implode(', ', $errors)
            ]);
        }
        
    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error occurred: ' . $e->getMessage()]);
    }
} else {
    // Handle single book submission
    // Validate required fields
    $required_fields = ['course_id', 'call_number', 'book_title', 'copyright', 'no_of_copies'];
    foreach ($required_fields as $field) {
        if (empty($input[$field])) {
            echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
            exit;
        }
    }
    
    try {
                // Validate location
        if (empty($input['location'])) {
            echo json_encode(['success' => false, 'message' => 'Location is required']);
            exit;
        }

        // Determine processing_status based on whether all required cataloging fields are filled
        // If call_number, no_of_copies, and location are all provided, set status to 'completed'
        $callNumber = trim($input['call_number'] ?? '');
        $noOfCopies = trim($input['no_of_copies'] ?? '');
        $location = trim($input['location'] ?? '');
        
        $processingStatus = 'processing'; // Default status
        if (!empty($callNumber) && !empty($noOfCopies) && !empty($location)) {
            $processingStatus = 'completed';
        }

        // Prepare SQL statement
        // Added created_by column - librarian creates the book reference       
        $sql = "INSERT INTO book_references (
            course_id,
            call_number,
            isbn,
            book_title,
            no_of_copies,
            publication_year,
            edition,
            author,
            publisher,
            location,
            processing_status,
            created_by,
            created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        // Bind parameters - added created_by (librarian user ID) and location  
        $stmt->bind_param("isssissssssi",
            $input['course_id'],
            $input['call_number'],
            $input['isbn'],
            $input['book_title'],
            $input['no_of_copies'],  // now maps directly to no_of_copies column
            $input['copyright'],     // maps to publication_year
            $input['edition'],
            $input['authors'],       // maps to author
            $input['publisher'],
            $input['location'],
            $processingStatus,        // processing_status: 'completed' if all fields filled, else 'processing'
            $createdBy                // created_by: librarian user ID
        );
        
        // Execute statement
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true, 
                'message' => 'Book added successfully',
                'book_id' => $conn->insert_id
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add book to database: ' . $stmt->error]);
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error occurred: ' . $e->getMessage()]);
    }
}

// End output buffering and send response
ob_end_flush();
?>
