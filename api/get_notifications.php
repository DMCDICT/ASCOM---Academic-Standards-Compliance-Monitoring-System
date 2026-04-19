<?php
// get_notifications.php - API endpoint to fetch notifications
header('Content-Type: application/json');

require_once __DIR__ . '/../bootstrap/database.php';

try {
    $pdo = ascom_get_pdo();
    
    // Get user role from session or request
    session_start();
    $userRole = $_SESSION['selected_role']['type'] ?? $_GET['role'] ?? 'all';
    $userId = $_SESSION['user_id'] ?? null;
    
    // Build query based on user role
    $query = "SELECT * FROM notifications WHERE 1=1";
    $params = [];
    
    if ($userRole === 'super_admin') {
        $query .= " AND (recipient_type = 'super_admin' OR recipient_type = 'all')";
    } elseif ($userRole === 'librarian') {
        $query .= " AND (recipient_type = 'librarian' OR recipient_type = 'all')";
    } elseif ($userRole === 'quality_assurance') {
        $query .= " AND (recipient_type = 'quality_assurance' OR recipient_type = 'all')";
    } elseif ($userRole === 'dean') {
        $query .= " AND (recipient_type = 'dean' OR recipient_type = 'all')";
    } elseif ($userRole === 'teacher') {
        $query .= " AND (recipient_type = 'teacher' OR recipient_type = 'all')";
    }
    
    // Filter out removed notifications (if column exists)
    // $query .= " AND (removed_at IS NULL OR removed_at = '')";
    
    // Add pagination
    $page = (int)($_GET['page'] ?? 1);
    $limit = (int)($_GET['limit'] ?? 10);
    $offset = ($page - 1) * $limit;
    
    $query .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get total count
    $countQuery = "SELECT COUNT(*) as total FROM notifications WHERE 1=1";
    if ($userRole === 'super_admin') {
        $countQuery .= " AND (recipient_type = 'super_admin' OR recipient_type = 'all')";
    } elseif ($userRole === 'librarian') {
        $countQuery .= " AND (recipient_type = 'librarian' OR recipient_type = 'all')";
    } elseif ($userRole === 'quality_assurance') {
        $countQuery .= " AND (recipient_type = 'quality_assurance' OR recipient_type = 'all')";
    } elseif ($userRole === 'dean') {
        $countQuery .= " AND (recipient_type = 'dean' OR recipient_type = 'all')";
    } elseif ($userRole === 'teacher') {
        $countQuery .= " AND (recipient_type = 'teacher' OR recipient_type = 'all')";
    }
    
    $countStmt = $pdo->query($countQuery);
    $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Format notifications
    $formattedNotifications = [];
    foreach ($notifications as $notification) {
        $ts = strtotime($notification['created_at']);
        $formattedNotifications[] = [
            'id' => $notification['id'],
            'title' => $notification['title'],
            'message' => $notification['message'],
            'type' => $notification['type'],
            'sender_name' => $notification['sender_name'],
            'sender_role' => $notification['sender_role'],
            'is_read' => (bool)$notification['is_read'],
            'created_at' => date('M d, Y H:i', $ts),
            'created_at_ts' => $ts,
            'read_at' => $notification['read_at'] ? date('M d, Y H:i', strtotime($notification['read_at'])) : null
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $formattedNotifications,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => ceil($totalCount / $limit),
            'total_count' => $totalCount,
            'has_next' => $page < ceil($totalCount / $limit),
            'has_prev' => $page > 1
        ]
    ]);
    
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
