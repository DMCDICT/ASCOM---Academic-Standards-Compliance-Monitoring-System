<?php
/**
 * Script to create the book_references table
 * Run this file once to set up the database table for book references
 */

// Include database connection
require_once 'includes/db_connection.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Setup Book References Table</title>
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
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #1976d2;
            padding-bottom: 10px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #28a745;
            margin: 10px 0;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #dc3545;
            margin: 10px 0;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #17a2b8;
            margin: 10px 0;
        }
        code {
            background: #f8f9fa;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>📚 Book References Table Setup</h1>";

try {
    // Check if table already exists
    $checkQuery = "SHOW TABLES LIKE 'book_references'";
    $result = $pdo->query($checkQuery);
    
    if ($result->rowCount() > 0) {
        echo "<div class='info'><strong>ℹ️ Table Already Exists:</strong> The <code>book_references</code> table already exists in the database.</div>";
    } else {
        // Create the table
        $createTableSQL = "
        CREATE TABLE IF NOT EXISTS `book_references` (
          `id` INT(11) NOT NULL AUTO_INCREMENT,
          `course_id` INT(11) NOT NULL,
          `title` VARCHAR(255) NOT NULL,
          `isbn` VARCHAR(20) NULL,
          `publisher` VARCHAR(150) NULL,
          `copyright_year` VARCHAR(10) NULL,
          `edition` VARCHAR(50) NULL,
          `location` VARCHAR(255) NULL,
          `call_number` VARCHAR(100) NULL,
          `created_by` INT(11) NULL COMMENT 'User ID of the person who created this reference',
          `requested_by` INT(11) NULL COMMENT 'User ID of the person who requested this reference',
          `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `idx_course_id` (`course_id`),
          KEY `idx_created_by` (`created_by`),
          KEY `idx_requested_by` (`requested_by`),
          CONSTRAINT `fk_book_ref_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
          CONSTRAINT `fk_book_ref_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
          CONSTRAINT `fk_book_ref_requested_by` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Stores book references for courses'
        ";
        
        $pdo->exec($createTableSQL);
        
        echo "<div class='success'><strong>✅ Success:</strong> Table <code>book_references</code> has been created successfully!</div>";
    }
    
    // Display table structure
    echo "<h2>📊 Table Structure</h2>";
    echo "<div class='info'>";
    echo "<strong>Fields:</strong><br>";
    echo "<ul>";
    echo "<li><code>id</code> - Primary key (auto increment)</li>";
    echo "<li><code>course_id</code> - Foreign key to courses table</li>";
    echo "<li><code>title</code> - Book reference title</li>";
    echo "<li><code>isbn</code> - ISBN number</li>";
    echo "<li><code>publisher</code> - Publisher name</li>";
    echo "<li><code>copyright_year</code> - Copyright year</li>";
    echo "<li><code>edition</code> - Edition information</li>";
    echo "<li><code>location</code> - Physical location</li>";
    echo "<li><code>call_number</code> - Library call number</li>";
    echo "<li><code>created_by</code> - User ID who created the reference</li>";
    echo "<li><code>requested_by</code> - User ID who requested the reference</li>";
    echo "<li><code>created_at</code> - Creation timestamp</li>";
    echo "<li><code>updated_at</code> - Last update timestamp</li>";
    echo "</ul>";
    echo "</div>";
    
    // Check table info
    $infoQuery = "
        SELECT 
            COUNT(*) as total_references,
            (SELECT COUNT(*) FROM courses) as total_courses
        FROM book_references
    ";
    $infoStmt = $pdo->query($infoQuery);
    $info = $infoStmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<h2>📈 Current Statistics</h2>";
    echo "<div class='info'>";
    echo "<ul>";
    echo "<li>Total Book References: <strong>{$info['total_references']}</strong></li>";
    echo "<li>Total Courses: <strong>{$info['total_courses']}</strong></li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<div class='success'><strong>✓ Setup Complete!</strong> You can now close this page.</div>";
    
} catch (Exception $e) {
    echo "<div class='error'><strong>❌ Error:</strong> " . htmlspecialchars($e->getMessage()) . "</div>";
    echo "<p>Please check your database connection and try again.</p>";
}

echo "
    </div>
</body>
</html>";

