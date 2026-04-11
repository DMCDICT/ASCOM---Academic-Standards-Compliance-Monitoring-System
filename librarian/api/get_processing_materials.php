<?php
// get_processing_materials.php
// API endpoint to fetch book references for Material Processing page

header('Content-Type: application/json');
require_once dirname(__FILE__) . '/../includes/db_connection.php';

$response = ['success' => false, 'data' => [], 'message' => ''];

try {
    // Get status filter from query parameter (default: processing for dashboard)
    $statusFilter = $_GET['status'] ?? 'processing';
    
    // Validate status filter
    if (!in_array($statusFilter, ['processing', 'completed', 'drafted'])) {
        $statusFilter = 'processing';
    }
    
    // Fetch book references filtered by status
    $query = "
        SELECT 
            br.id,
            br.book_title,
            br.author,
            br.isbn,
            br.publisher,
            br.publication_year,
            br.edition,
            br.location,
            br.call_number,
            br.processing_status,
            br.status_reason,
            br.course_id,
            c.course_code,
            c.course_title,
            br.created_by,
            br.requested_by,
            TRIM(CONCAT(COALESCE(uc.first_name, ''), ' ', COALESCE(uc.last_name, ''))) AS created_by_name,
            TRIM(CONCAT(COALESCE(ur.first_name, ''), ' ', COALESCE(ur.last_name, ''))) AS requested_by_name,
            COALESCE(dc.department_code, dr.department_code, 'CCS') AS department_code,
            COALESCE(dc.color_code, dr.color_code, '#C41E3A') AS department_color,
            br.created_at,
            br.updated_at
        FROM book_references br
        LEFT JOIN courses c ON br.course_id = c.id
        LEFT JOIN users uc ON br.created_by = uc.id
        LEFT JOIN users ur ON br.requested_by = ur.id
        LEFT JOIN departments dc ON uc.department_id = dc.id
        LEFT JOIN departments dr ON ur.department_id = dr.id
        WHERE br.processing_status = ?
        ORDER BY br.created_at DESC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$statusFilter]);
    $materials = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format the data to match the Material Processing card structure
    $formattedMaterials = array_map(function($material) {
        // Format as APA 7th edition
        $apaFormat = '';
        
        // Author(s)
        if (!empty($material['author'])) {
            $apaFormat .= trim($material['author']);
        }
        
        // Publication year
        if (!empty($material['publication_year'])) {
            $apaFormat .= ' (' . $material['publication_year'] . ')';
        } elseif (!empty($material['author'])) {
            $apaFormat .= ' (n.d.)';
        }
        
        // Book title - italicized (HTML)
        if (!empty($material['book_title'])) {
            $apaFormat .= '. ' . trim($material['book_title']);
        }
        
        // Edition (if not 1st)
        if (!empty($material['edition']) && strpos(strtolower($material['edition']), '1st') === false && strpos(strtolower($material['edition']), 'first') === false) {
            $apaFormat .= ' (' . trim($material['edition']) . ')';
        }
        
        // Publisher
        if (!empty($material['publisher'])) {
            $apaFormat .= '. ' . trim($material['publisher']);
        }
        
        // Determine if this was requested by teacher or created by dean
        $hasRequester = !empty($material['requested_by_name']) && trim($material['requested_by_name']) !== '';
        $requesterName = $hasRequester ? trim($material['requested_by_name']) : trim($material['created_by_name']);
        
        // Fallback if name is still empty
        if (empty($requesterName)) {
            $requesterName = 'Unknown User';
        }
        
        // Add "Dr." title for department deans
        if (!$hasRequester && !preg_match('/^Dr\.?\s/i', $requesterName)) {
            $requesterName = 'Dr. ' . $requesterName;
        }
        
        $departmentCode = !empty($material['department_code']) ? $material['department_code'] : 'CCS';
        $departmentColor = !empty($material['department_color']) ? $material['department_color'] : '#C41E3A';
        $requesterRole = $hasRequester ? ($departmentCode . ' FACULTY') : ($departmentCode . ' DEAN');
        
        return [
            'id' => $material['id'],
            'requesterName' => $requesterName,
            'requesterRole' => $requesterRole,
            'departmentCode' => $departmentCode,
            'departmentColor' => $departmentColor,
            'courseCode' => $material['course_code'] ?? 'N/A',
            'courseName' => $material['course_title'] ?? 'N/A',
            'materialTitle' => !empty($apaFormat) ? $apaFormat : 'N/A',
            'status' => $material['processing_status'] ?? 'processing',
            'progress' => 0, // Will be calculated based on status
            'requestDate' => $material['created_at'] ?? date('Y-m-d'),
            'estimatedCompletion' => 'TBD',
            'course_id' => $material['course_id']
        ];
    }, $materials);
    
    $response['success'] = true;
    $response['data'] = $formattedMaterials;
    $response['message'] = 'Processing materials fetched successfully';
    
} catch (Exception $e) {
    $response['message'] = 'Failed to fetch processing materials: ' . $e->getMessage();
}

echo json_encode($response);
exit;

