<?php
// setup_classifications_table.php
// Web-based setup script to create the classifications table

// Start session to get user info
session_start();

// Database connection
require_once dirname(__FILE__) . '/includes/db_connection.php';

$messages = [];
$success = false;

// Execute SQL to create table
if (isset($_POST['create_table'])) {
    try {
        // Create table SQL directly
        $sql = "CREATE TABLE IF NOT EXISTS `classifications` (
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
        
        // Execute the SQL
        $pdo->exec($sql);
        
        $messages[] = "✅ Classifications table created successfully!";
        $success = true;
        
        // Check if table exists
        $checkQuery = "SHOW TABLES LIKE 'classifications'";
        $result = $pdo->query($checkQuery);
        if ($result->rowCount() > 0) {
            $messages[] = "✅ Table verified and ready to use!";
        }
        
    } catch (Exception $e) {
        $messages[] = "❌ Error: " . $e->getMessage();
        error_log("Database setup error: " . $e->getMessage());
    }
}

// Check if table already exists
$tableExists = false;
try {
    $checkQuery = "SHOW TABLES LIKE 'classifications'";
    $result = $pdo->query($checkQuery);
    $tableExists = ($result->rowCount() > 0);
} catch (Exception $e) {
    $messages[] = "Info: Could not check if table exists: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Classifications Table</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        .message {
            padding: 15px;
            margin: 10px 0;
            border-radius: 6px;
            font-weight: 500;
        }
        .success {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #4caf50;
        }
        .info {
            background: #e3f2fd;
            color: #1565c0;
            border: 1px solid #2196f3;
        }
        .error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #f44336;
        }
        button {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            margin: 10px 5px;
        }
        button:hover {
            background: #45a049;
        }
        .info-box {
            background: #fff3e0;
            border: 1px solid #ff9800;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .info-box strong {
            color: #e65100;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Setup Classifications Database Table</h1>
        
        <?php if ($tableExists): ?>
            <div class="message info">
                ✅ The 'classifications' table already exists in the database.
            </div>
            <p>The table is ready to use. You can now add classifications through the dashboard.</p>
        <?php else: ?>
            <div class="info-box">
                <strong>What this does:</strong><br>
                Creates the 'classifications' table needed for managing library classification systems.<br>
                This includes fields for name, call number range, description, status, and metadata.
            </div>
            
            <?php if (!empty($messages)): ?>
                <?php foreach ($messages as $msg): ?>
                    <div class="message <?php echo $success ? 'success' : 'error'; ?>">
                        <?php echo htmlspecialchars($msg); ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <form method="POST">
                <button type="submit" name="create_table">Create Classifications Table</button>
                <button type="button" onclick="window.location.href='content.php?page=dashboard'">Back to Dashboard</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>

