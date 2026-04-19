<?php
// super_admin-mis/api/upload_avatar.php
// API endpoint to handle profile picture uploads for super admins and users.

header('Content-Type: application/json');

// Include session and database connection
require_once __DIR__ . '/../../super_admin_session_config.php';
require_once __DIR__ . '/../../session_config.php';
require_once __DIR__ . '/../includes/db_connection.php';

// Authentication check
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['is_authenticated'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

try {
    // Check if file was uploaded
    if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
        $errorCode = $_FILES['avatar']['error'] ?? 'unknown';
        throw new Exception("File upload failed with error code: $errorCode");
    }

    $file = $_FILES['avatar'];
    $fileSize = $file['size'];
    $fileTmpPath = $file['tmp_name'];
    $fileName = $file['name'];
    $fileType = $file['type'];

    // 1. Validate File Size (Max 2MB)
    if ($fileSize > 2 * 1024 * 1024) {
        throw new Exception("File is too large. Maximum size is 2MB.");
    }

    // 2. Validate File Type
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($fileType, $allowedTypes)) {
        throw new Exception("Invalid file type. Only JPG, PNG, and WEBP are allowed.");
    }

    // 3. Prepare Upload Directory
    // Since we are in /super_admin-mis/api/, root is ../../
    $uploadDir = __DIR__ . '/../../storage/avatars/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // 4. Generate Unique Filename
    $userId = $_SESSION['user_id'];
    $userRole = $_SESSION['user_role'] ?? 'user';
    $extension = pathinfo($fileName, PATHINFO_EXTENSION);
    if (empty($extension)) {
        $extension = ($fileType === 'image/png') ? 'png' : (($fileType === 'image/webp') ? 'webp' : 'jpg');
    }
    $newFileName = 'avatar_' . $userRole . '_' . $userId . '_' . time() . '.' . $extension;
    $destPath = $uploadDir . $newFileName;

    // 5. Move File
    if (!move_uploaded_file($fileTmpPath, $destPath)) {
        throw new Exception("Failed to save uploaded file.");
    }

    // 6. Update Database
    // Decide which table to update based on user role
    $table = ($userRole === 'super_admin') ? 'super_admin' : 'users';
    
    $updateQuery = "UPDATE $table SET profile_image = ? WHERE id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("si", $newFileName, $userId);
    
    if (!$stmt->execute()) {
        // Clean up uploaded file on DB failure
        unlink($destPath);
        throw new Exception("Database update failed: " . $stmt->error);
    }
    $stmt->close();

    // 7. Update Session
    $_SESSION['profile_image'] = $newFileName;

    // Return success
    echo json_encode([
        'success' => true,
        'message' => 'Profile picture updated successfully.',
        'avatar_url' => 'http://' . $_SERVER['HTTP_HOST'] . '/storage/avatars/' . $newFileName,
        'filename' => $newFileName
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>
