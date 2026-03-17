<?php
// create_notification.php - API endpoint to create new notifications
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    $pdo = new PDO('mysql:host=localhost;dbname=ascom_db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    $title = $input['title'] ?? '';
    $message = $input['message'] ?? '';
    $type = $input['type'] ?? 'info';
    $senderId = $input['sender_id'] ?? null;
    $senderName = $input['sender_name'] ?? '';
    $senderRole = $input['sender_role'] ?? '';
    $recipientType = $input['recipient_type'] ?? 'all';
    $recipientId = $input['recipient_id'] ?? null;
    
    if (empty($title) || empty($message)) {
        echo json_encode(['success' => false, 'error' => 'Title and message are required']);
        exit;
    }
    
    // Create notification
    $stmt = $pdo->prepare("
        INSERT INTO notifications (title, message, type, sender_id, sender_name, sender_role, recipient_type, recipient_id) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $title,
        $message,
        $type,
        $senderId,
        $senderName,
        $senderRole,
        $recipientType,
        $recipientId
    ]);
    
    $notificationId = $pdo->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'message' => 'Notification created successfully',
        'notification_id' => $notificationId
    ]);
    
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
