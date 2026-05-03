<?php
// update_classification.php
// API endpoint to update an existing classification

header('Content-Type: application/json');
require_once dirname(__FILE__) . '/../includes/db_connection.php';
require_once dirname(dirname(__FILE__)) . '/../session_config.php';

$response = ['success' => false, 'message' => ''];

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method';
    echo json_encode($response);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $id = intval($input['id'] ?? 0);
    $name = trim($input['name'] ?? '');
    $callNumberRange = trim($input['call_number_range'] ?? '');
    $location = trim($input['location'] ?? '');
    $description = trim($input['description'] ?? '');
    $type = trim($input['type'] ?? 'DDC');
    $status = $input['status'] ?? 'active';
    
    if (empty($id)) {
        throw new Exception('Classification ID is required');
    }
    
    if (empty($name)) {
        throw new Exception('Classification name is required');
    }
    
    if (empty($callNumberRange)) {
        throw new Exception('Call number range is required');
    }
    
    if (empty($location)) {
        throw new Exception('Library location is required');
    }
    
    // Validate call number range format
    if (!preg_match('/^\d{3}-\d{3}$/', $callNumberRange)) {
        throw new Exception('Call number range must be in format XXX-XXX (e.g., 000-099)');
    }
    
    // Validate status
    if (!in_array($status, ['active', 'inactive'])) {
        $status = 'active';
    }
    
    // Check if classification exists
    $checkStmt = $pdo->prepare("SELECT id FROM classifications WHERE id = ?");
    $checkStmt->execute([$id]);
    if (!$checkStmt->fetch()) {
        throw new Exception('Classification not found');
    }
    
    // Check for duplicate call number range (excluding current record)
    $dupCheck = $pdo->prepare("
        SELECT id FROM classifications 
        WHERE call_number_range = ? 
        AND id != ? 
        AND (location = ? OR library_location_id = (
            SELECT id FROM library_locations WHERE name = ? LIMIT 1
        ))
    ");
    $dupCheck->execute([$callNumberRange, $id, $location, $location]);
    if ($dupCheck->fetch()) {
        throw new Exception('A classification with this call number range already exists for this library location');
    }
    
    // Resolve library_location_id
    $locationId = null;
    $locSelect = $pdo->prepare("SELECT id FROM library_locations WHERE name = ? LIMIT 1");
    $locSelect->execute([$location]);
    $locRow = $locSelect->fetch(PDO::FETCH_ASSOC);
    if ($locRow && isset($locRow['id'])) {
        $locationId = (int)$locRow['id'];
    }
    
    // Update the classification
    $updateQuery = "
        UPDATE classifications SET
            name = ?,
            type = ?,
            call_number_range = ?,
            description = ?,
            status = ?,
            location = ?,
            library_location_id = ?,
            updated_at = NOW()
        WHERE id = ?
    ";
    
    $updateStmt = $pdo->prepare($updateQuery);
    $updateStmt->execute([
        $name,
        $type,
        $callNumberRange,
        $description,
        $status,
        $location,
        $locationId,
        $id
    ]);
    
    $response['success'] = true;
    $response['message'] = 'Classification updated successfully';
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
exit;