<?php
// remove_notification.php - API endpoint to remove notification (soft delete)
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
    
    if (!$notificationId) {
        echo json_encode(['success' => false, 'error' => 'Notification ID required']);
        exit;
    }
    
    // Soft delete notification by marking it as removed (we'll add a removed_at column)
    // For now, we'll use a different approach - mark it as hidden
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id = ?");
    $stmt->execute([$notificationId]);
    
    // Actually, let's add a removed column to the notifications table
    // First check if the column exists, if not add it
    try {
        $pdo->exec("ALTER TABLE notifications ADD COLUMN removed_at TIMESTAMP NULL");
    } catch (Exception $e) {
        // Column might already exist, ignore error
    }
    
    // Mark as removed
    $stmt = $pdo->prepare("UPDATE notifications SET removed_at = NOW() WHERE id = ?");
    $stmt->execute([$notificationId]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Notification removed']);
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
