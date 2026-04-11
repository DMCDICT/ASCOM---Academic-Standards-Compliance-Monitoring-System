<?php
// get_classifications.php
// API endpoint to fetch all classifications

header('Content-Type: application/json');
require_once dirname(__FILE__) . '/../includes/db_connection.php';

$response = ['success' => false, 'data' => [], 'message' => ''];

try {
    // Check if classifications table exists, if not create it automatically
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'classifications'");
    if ($tableCheck->rowCount() == 0) {
        // Automatically create the table if it doesn't exist
        try {
            $createTableSQL = "CREATE TABLE IF NOT EXISTS `classifications` (
              `id` INT(11) NOT NULL AUTO_INCREMENT,
              `name` VARCHAR(255) NOT NULL,
              `type` VARCHAR(50) NOT NULL DEFAULT 'DDC' COMMENT 'Classification type (DDC, LCC, etc.)',
              `call_number_range` VARCHAR(20) NOT NULL COMMENT 'Range like 000-099',
              `description` TEXT NULL,
              `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
              `created_by` INT(11) NULL COMMENT 'User ID who created this classification',
              `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              UNIQUE KEY `unique_call_number_range` (`call_number_range`),
              KEY `idx_status` (`status`),
              KEY `idx_type` (`type`),
              KEY `idx_created_by` (`created_by`),
              CONSTRAINT `fk_classifications_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Library classification systems (Dewey Decimal, etc.)'";
            
            $pdo->exec($createTableSQL);
        } catch (Exception $e) {
            // If creation fails, return empty array
            $response['success'] = true;
            $response['data'] = [];
            $response['message'] = 'Classifications table does not exist and could not be created automatically. Please run the setup script.';
            echo json_encode($response);
            exit;
        }
    }
    
    // Ensure library_locations table exists (if we ever added classifications with locations)
    try {
        $locTableCheck = $pdo->query("SHOW TABLES LIKE 'library_locations'");
        if ($locTableCheck->rowCount() == 0) {
            $createLocationSQL = "CREATE TABLE IF NOT EXISTS `library_locations` (
              `id` INT(11) NOT NULL AUTO_INCREMENT,
              `name` VARCHAR(100) NOT NULL,
              `code` VARCHAR(50) NULL,
              `is_active` TINYINT(1) NOT NULL DEFAULT 1,
              `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uniq_library_location_name` (`name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Physical library locations (Main, Buenavista, etc.)'";

            $pdo->exec($createLocationSQL);
        }
    } catch (Exception $e) {
    }

    // Fetch all classifications with optional library location
    $query = "
        SELECT 
            c.id,
            c.name,
            c.type,
            c.call_number_range,
            c.description,
            c.status,
            c.created_by,
            c.created_at,
            c.updated_at,
            c.location,
            c.library_location_id,
            l.name AS library_location_name
        FROM classifications c
        LEFT JOIN library_locations l ON c.library_location_id = l.id
        ORDER BY c.call_number_range ASC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $classifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Count books for each classification (optional enhancement)
    foreach ($classifications as &$classification) {
        $rangeParts = explode('-', $classification['call_number_range']);
        $minRange = isset($rangeParts[0]) ? intval($rangeParts[0]) : 0;
        $maxRange = isset($rangeParts[1]) ? intval($rangeParts[1]) : 999;
        
        // Count books in this range (fetch all and filter in PHP for accuracy)
        try {
            $countQuery = "
                SELECT call_number
                FROM book_references
                WHERE call_number IS NOT NULL 
                AND call_number != ''
            ";
            
            $countStmt = $pdo->prepare($countQuery);
            $countStmt->execute();
            $allCallNumbers = $countStmt->fetchAll(PDO::FETCH_COLUMN);
            
            $count = 0;
            foreach ($allCallNumbers as $callNumber) {
                // Extract first 3 digits
                preg_match('/^(\d{1,3})/', trim($callNumber), $matches);
                if (isset($matches[1])) {
                    $firstDigits = intval($matches[1]);
                    $paddedDigits = str_pad($firstDigits, 3, '0', STR_PAD_LEFT);
                    $number = intval($paddedDigits);
                    if ($number >= $minRange && $number <= $maxRange) {
                        $count++;
                    }
                }
            }
            
            $classification['totalItems'] = $count;
        } catch (Exception $e) {
            // If counting fails, set to 0
            $classification['totalItems'] = 0;
        }
        
        // Format dates
        $classification['lastUpdated'] = $classification['updated_at'] ?? $classification['created_at'];
    }
    
    $response['success'] = true;
    $response['data'] = $classifications;
    $response['message'] = 'Classifications fetched successfully';
    
} catch (Exception $e) {
    $response['message'] = 'Failed to fetch classifications: ' . $e->getMessage();
}

echo json_encode($response);
exit;

