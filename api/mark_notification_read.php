<?php
// mark_notification_read.php - API endpoint to mark notification as read
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    $pdo = new PDO('mysql:host=localhost;dbname=ascom_db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $input = json_decode(file_get_contents('php://input'), true);
    $notificationId = $input['notification_id'] ?? null;
    $markAsRead = $input['mark_as_read'] ?? true; // Default to marking as read
    
    if (!$notificationId) {
        echo json_encode(['success' => false, 'error' => 'Notification ID required']);
        exit;
    }
    
    // Toggle notification read status
    if ($markAsRead) {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id = ?");
    } else {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 0, read_at = NULL WHERE id = ?");
    }
    $stmt->execute([$notificationId]);
    
    if ($stmt->rowCount() > 0) {
        $status = $markAsRead ? 'read' : 'unread';
        echo json_encode(['success' => true, 'message' => "Notification marked as {$status}"]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Notification not found']);
    }
    
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
