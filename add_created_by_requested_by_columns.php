<?php
/**
 * Script to add created_by and requested_by columns to book_references table
 * This script safely checks if columns exist before adding them
 */

require_once 'department-dean/includes/db_connection.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Book References Columns</title>
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
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        .message {
            padding: 15px;
            margin: 10px 0;
            border-radius: 4px;
            font-size: 14px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Add created_by and requested_by Columns</h1>
        
        <?php
        try {
            // Check if table exists
            $checkTable = $pdo->query("SHOW TABLES LIKE 'book_references'");
            if ($checkTable->rowCount() === 0) {
                echo '<div class="error"><strong>❌ Error:</strong> Table <code>book_references</code> does not exist. Please create it first.</div>';
                exit;
            }
            
            echo '<div class="info"><strong>ℹ️ Table Found:</strong> The <code>book_references</code> table exists.</div>';
            
            // Check if created_by column exists
            $checkCreatedBy = $pdo->query("SHOW COLUMNS FROM book_references LIKE 'created_by'");
            $createdByExists = $checkCreatedBy->rowCount() > 0;
            
            // Check if requested_by column exists
            $checkRequestedBy = $pdo->query("SHOW COLUMNS FROM book_references LIKE 'requested_by'");
            $requestedByExists = $checkRequestedBy->rowCount() > 0;
            
            if ($createdByExists && $requestedByExists) {
                echo '<div class="success"><strong>✅ Columns Already Exist:</strong> Both <code>created_by</code> and <code>requested_by</code> columns already exist in the table.</div>';
            } else {
                // Add created_by if it doesn't exist
                if (!$createdByExists) {
                    try {
                        $alterQuery = "ALTER TABLE book_references ADD COLUMN `created_by` INT(11) NULL COMMENT 'User ID of the person who created this reference'";
                        $pdo->exec($alterQuery);
                        echo '<div class="success"><strong>✅ Success:</strong> Column <code>created_by</code> has been added.</div>';
                    } catch (PDOException $e) {
                        echo '<div class="error"><strong>❌ Error:</strong> Failed to add <code>created_by</code> column: ' . htmlspecialchars($e->getMessage()) . '</div>';
                    }
                } else {
                    echo '<div class="info"><strong>ℹ️ Column Exists:</strong> Column <code>created_by</code> already exists.</div>';
                }
                
                // Add requested_by if it doesn't exist
                if (!$requestedByExists) {
                    try {
                        $alterQuery = "ALTER TABLE book_references ADD COLUMN `requested_by` INT(11) NULL COMMENT 'User ID of the person who requested this reference'";
                        $pdo->exec($alterQuery);
                        echo '<div class="success"><strong>✅ Success:</strong> Column <code>requested_by</code> has been added.</div>';
                    } catch (PDOException $e) {
                        echo '<div class="error"><strong>❌ Error:</strong> Failed to add <code>requested_by</code> column: ' . htmlspecialchars($e->getMessage()) . '</div>';
                    }
                } else {
                    echo '<div class="info"><strong>ℹ️ Column Exists:</strong> Column <code>requested_by</code> already exists.</div>';
                }
                
                // Add indexes
                try {
                    // Check and add index for created_by
                    $checkIndex = $pdo->query("SHOW INDEXES FROM book_references WHERE Key_name = 'idx_created_by'");
                    if ($checkIndex->rowCount() === 0) {
                        $pdo->exec("CREATE INDEX idx_created_by ON book_references (created_by)");
                        echo '<div class="success"><strong>✅ Success:</strong> Index <code>idx_created_by</code> has been created.</div>';
                    }
                } catch (PDOException $e) {
                    echo '<div class="warning"><strong>⚠️ Warning:</strong> Could not create index for <code>created_by</code>: ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
                
                try {
                    // Check and add index for requested_by
                    $checkIndex = $pdo->query("SHOW INDEXES FROM book_references WHERE Key_name = 'idx_requested_by'");
                    if ($checkIndex->rowCount() === 0) {
                        $pdo->exec("CREATE INDEX idx_requested_by ON book_references (requested_by)");
                        echo '<div class="success"><strong>✅ Success:</strong> Index <code>idx_requested_by</code> has been created.</div>';
                    }
                } catch (PDOException $e) {
                    echo '<div class="warning"><strong>⚠️ Warning:</strong> Could not create index for <code>requested_by</code>: ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
                
                // Add foreign keys (optional - may fail if users table doesn't exist or has constraints)
                try {
                    $checkFK = $pdo->query("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'book_references' AND CONSTRAINT_NAME = 'fk_book_ref_created_by'");
                    if ($checkFK->rowCount() === 0) {
                        $pdo->exec("ALTER TABLE book_references ADD CONSTRAINT fk_book_ref_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL");
                        echo '<div class="success"><strong>✅ Success:</strong> Foreign key <code>fk_book_ref_created_by</code> has been added.</div>';
                    }
                } catch (PDOException $e) {
                    echo '<div class="warning"><strong>⚠️ Warning:</strong> Could not add foreign key for <code>created_by</code>: ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
                
                try {
                    $checkFK = $pdo->query("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'book_references' AND CONSTRAINT_NAME = 'fk_book_ref_requested_by'");
                    if ($checkFK->rowCount() === 0) {
                        $pdo->exec("ALTER TABLE book_references ADD CONSTRAINT fk_book_ref_requested_by FOREIGN KEY (requested_by) REFERENCES users(id) ON DELETE SET NULL");
                        echo '<div class="success"><strong>✅ Success:</strong> Foreign key <code>fk_book_ref_requested_by</code> has been added.</div>';
                    }
                } catch (PDOException $e) {
                    echo '<div class="warning"><strong>⚠️ Warning:</strong> Could not add foreign key for <code>requested_by</code>: ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
            }
            
            echo '<div class="info"><strong>ℹ️ Column Usage:</strong><br>';
            echo '- <code>created_by</code>: Will be filled with the Department Dean or Librarian user ID when they add a book reference<br>';
            echo '- <code>requested_by</code>: Will be filled with the Teacher (Faculty) user ID when they request a book reference</div>';
            
        } catch (PDOException $e) {
            echo '<div class="error"><strong>❌ Database Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        ?>
    </div>
</body>
</html>

