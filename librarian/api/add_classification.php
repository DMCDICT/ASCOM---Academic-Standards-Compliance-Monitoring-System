<?php
// add_classification.php
// API endpoint to add a new classification

header('Content-Type: application/json');
require_once dirname(__FILE__) . '/../includes/db_connection.php';
// Use the same session configuration as the rest of the app (so we see the logged-in user)
require_once dirname(dirname(__FILE__)) . '/../session_config.php';

$response = ['success' => false, 'message' => ''];

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method';
    echo json_encode($response);
    exit;
}

// Check if classifications table exists, if not create it automatically
try {
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'classifications'");
    if ($tableCheck->rowCount() == 0) {
        // Automatically create the table if it doesn't exist
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
          KEY `idx_created_by` (`created_by`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Library classification systems (Dewey Decimal, etc.)'";
        
        // Try to create table, but ignore foreign key constraint if users table has issues
        try {
            $pdo->exec($createTableSQL);
            error_log("Classifications table created automatically");
        } catch (Exception $e) {
            // If foreign key fails, try without it
            $createTableSQLNoFK = "CREATE TABLE IF NOT EXISTS `classifications` (
              `id` INT(11) NOT NULL AUTO_INCREMENT,
              `name` VARCHAR(255) NOT NULL,
              `type` VARCHAR(50) NOT NULL DEFAULT 'DDC',
              `call_number_range` VARCHAR(20) NOT NULL,
              `description` TEXT NULL,
              `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
              `created_by` INT(11) NULL,
              `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              UNIQUE KEY `unique_call_number_range` (`call_number_range`),
              KEY `idx_status` (`status`),
              KEY `idx_type` (`type`),
              KEY `idx_created_by` (`created_by`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
            $pdo->exec($createTableSQLNoFK);
            error_log("Classifications table created automatically (without foreign key)");
        }
    }
} catch (Exception $e) {
    error_log("Error checking/creating classifications table: " . $e->getMessage());
}

// Check if library_locations table exists, if not create it automatically
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
        error_log("library_locations table created automatically");
    }
} catch (Exception $e) {
    error_log("Error checking/creating library_locations table: " . $e->getMessage());
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $name = trim($input['name'] ?? '');
    $type = trim($input['type'] ?? 'DDC');
    $callNumberRange = trim($input['call_number_range'] ?? '');
    $location = trim($input['location'] ?? '');
    $description = trim($input['description'] ?? '');
    $status = $input['status'] ?? 'active';
    
    // Get user ID from shared session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $createdBy = $_SESSION['user_id'] ?? null;
    
    // Validate required fields
    if (empty($name)) {
        throw new Exception('Classification name is required');
    }
    
    if (empty($callNumberRange)) {
        throw new Exception('Call number range is required');
    }
    
    // Validate call number range format (e.g., "000-099")
    if (!preg_match('/^\d{3}-\d{3}$/', $callNumberRange)) {
        throw new Exception('Call number range must be in format XXX-XXX (e.g., 000-099)');
    }

    // Validate location
    if (empty($location)) {
        throw new Exception('Library location is required');
    }
    
    // Validate status
    if (!in_array($status, ['active', 'inactive'])) {
        $status = 'active';
    }
    
    // Validate type
    if (empty($type)) {
        $type = 'DDC';
    }
    
    // Resolve library_location_id from library_locations table (create if missing)
    $locationId = null;
    $locSelect = $pdo->prepare("SELECT id FROM library_locations WHERE name = ? LIMIT 1");
    $locSelect->execute([$location]);
    $locRow = $locSelect->fetch(PDO::FETCH_ASSOC);
    if ($locRow && isset($locRow['id'])) {
        $locationId = (int)$locRow['id'];
    } else {
        $locInsert = $pdo->prepare("INSERT INTO library_locations (name, is_active, created_at) VALUES (?, 1, NOW())");
        $locInsert->execute([$location]);
        $locationId = (int)$pdo->lastInsertId();
    }

    // Ensure location & library_location_id columns exist (for older installs)
    $columnCheck = $pdo->query("SHOW COLUMNS FROM classifications LIKE 'location'");
    if ($columnCheck->rowCount() === 0) {
        $pdo->exec("ALTER TABLE classifications ADD COLUMN location VARCHAR(100) NULL AFTER status");
    }
    $locIdColumnCheck = $pdo->query("SHOW COLUMNS FROM classifications LIKE 'library_location_id'");
    if ($locIdColumnCheck->rowCount() === 0) {
        $pdo->exec("ALTER TABLE classifications ADD COLUMN library_location_id INT(11) NULL AFTER location");
        $pdo->exec("CREATE INDEX idx_library_location_id ON classifications(library_location_id)");
    }

    // Check if call number range already exists for this location (text + id to stay compatible)
    $checkQuery = "SELECT id FROM classifications 
                   WHERE call_number_range = ? 
                     AND (library_location_id = ? OR (library_location_id IS NULL AND location = ?))";
    $checkStmt = $pdo->prepare($checkQuery);
    $checkStmt->execute([$callNumberRange, $locationId, $location]);
    if ($checkStmt->fetch()) {
        throw new Exception('A classification with this call number range already exists for this library location');
    }

    // Insert new classification with location & library_location_id
    $insertQuery = "
        INSERT INTO classifications (
            name,
            type,
            call_number_range,
            description,
            status,
            location,
            library_location_id,
            created_by,
            created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ";
    
    $stmt = $pdo->prepare($insertQuery);
    $stmt->execute([
        $name,
        $type,
        $callNumberRange,
        $description,
        $status,
        $location,
        $locationId,
        $createdBy
    ]);
    
    $response['success'] = true;
    $response['message'] = 'Classification added successfully';
    $response['id'] = $pdo->lastInsertId();
    
} catch (Exception $e) {
    error_log("Error adding classification: " . $e->getMessage());
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
exit;

