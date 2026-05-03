<?php
/**
 * GET /librarian/api/get_departments.php
 *
 * Returns departments for multi-select assignment.
 * Response: { success: true, data: [{ id, department_name, department_code, color_code }] }
 */

require_once dirname(__DIR__, 2) . '/bootstrap/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $pdo = ascom_get_pdo();

    $stmt = $pdo->query("
        SELECT id, department_name, department_code, COALESCE(color_code, '#666666') AS color_code
        FROM departments
        ORDER BY department_name ASC
    ");

    echo json_encode([
        'success' => true,
        'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

