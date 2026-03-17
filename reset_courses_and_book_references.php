<?php
/**
 * Reset/Erase Courses and Book References Tables
 * This script will DELETE all data from book_references and courses tables
 * 
 * WARNING: This will permanently delete all courses and book references!
 * Use with caution!
 */

require_once 'super_admin-mis/includes/db_connection.php';

// Set content type
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Courses and Book References</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #d32f2f;
            border-bottom: 2px solid #d32f2f;
            padding-bottom: 10px;
        }
        .warning {
            background-color: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
            color: #856404;
        }
        .warning strong {
            color: #d32f2f;
        }
        .info {
            background-color: #e3f2fd;
            border: 1px solid #2196f3;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
        }
        .success {
            background-color: #d4edda;
            border: 1px solid #28a745;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            border: 1px solid #dc3545;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
            color: #721c24;
        }
        button {
            background-color: #d32f2f;
            color: white;
            border: none;
            padding: 12px 24px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            margin: 10px 5px;
        }
        button:hover {
            background-color: #b71c1c;
        }
        button.secondary {
            background-color: #6c757d;
        }
        button.secondary:hover {
            background-color: #5a6268;
        }
        .stats {
            margin: 20px 0;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .stats table {
            width: 100%;
            border-collapse: collapse;
        }
        .stats table td {
            padding: 8px;
            border-bottom: 1px solid #dee2e6;
        }
        .stats table td:first-child {
            font-weight: bold;
            width: 200px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>⚠️ Reset Courses and Book References</h1>
        
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_reset'])) {
            try {
                // Get current counts before deletion
                $countBooks = $pdo->query("SELECT COUNT(*) as count FROM book_references")->fetch(PDO::FETCH_ASSOC)['count'];
                $countCourses = $pdo->query("SELECT COUNT(*) as count FROM courses")->fetch(PDO::FETCH_ASSOC)['count'];
                
                // Disable foreign key checks temporarily to avoid issues
                $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
                
                // Delete all book references first
                $deleteBooks = $pdo->exec("DELETE FROM book_references");
                
                // Delete all courses
                $deleteCourses = $pdo->exec("DELETE FROM courses");
                
                // Reset AUTO_INCREMENT
                $pdo->exec("ALTER TABLE book_references AUTO_INCREMENT = 1");
                $pdo->exec("ALTER TABLE courses AUTO_INCREMENT = 1");
                
                // Re-enable foreign key checks
                $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
                
                echo '<div class="success">';
                echo '<strong>✅ Success!</strong><br>';
                echo "Deleted <strong>{$countBooks}</strong> book reference(s) from <code>book_references</code> table.<br>";
                echo "Deleted <strong>{$countCourses}</strong> course(s) from <code>courses</code> table.<br>";
                echo "Auto-increment counters have been reset.";
                echo '</div>';
                
            } catch (PDOException $e) {
                echo '<div class="error">';
                echo '<strong>❌ Error:</strong><br>';
                echo htmlspecialchars($e->getMessage());
                echo '</div>';
            }
        } else {
            // Show current statistics
            try {
                $countBooks = $pdo->query("SELECT COUNT(*) as count FROM book_references")->fetch(PDO::FETCH_ASSOC)['count'];
                $countCourses = $pdo->query("SELECT COUNT(*) as count FROM courses")->fetch(PDO::FETCH_ASSOC)['count'];
                
                echo '<div class="warning">';
                echo '<strong>⚠️ WARNING:</strong><br>';
                echo 'This action will <strong>PERMANENTLY DELETE</strong> all data from both tables:<br>';
                echo '<ul>';
                echo '<li><code>book_references</code> table</li>';
                echo '<li><code>courses</code> table</li>';
                echo '</ul>';
                echo 'This action <strong>CANNOT BE UNDONE!</strong>';
                echo '</div>';
                
                echo '<div class="stats">';
                echo '<h3>Current Data:</h3>';
                echo '<table>';
                echo '<tr><td>Book References:</td><td>' . number_format($countBooks) . ' record(s)</td></tr>';
                echo '<tr><td>Courses:</td><td>' . number_format($countCourses) . ' record(s)</td></tr>';
                echo '</table>';
                echo '</div>';
                
                if ($countBooks > 0 || $countCourses > 0) {
                    echo '<form method="POST" onsubmit="return confirm(\'Are you absolutely sure you want to delete ALL courses and book references? This action cannot be undone!\');">';
                    echo '<input type="hidden" name="confirm_reset" value="1">';
                    echo '<button type="submit" style="background-color: #d32f2f;">🗑️ Delete All Data</button>';
                    echo '<a href="javascript:history.back()"><button type="button" class="secondary">Cancel</button></a>';
                    echo '</form>';
                } else {
                    echo '<div class="info">';
                    echo 'Both tables are already empty. Nothing to delete.';
                    echo '</div>';
                }
                
            } catch (PDOException $e) {
                echo '<div class="error">';
                echo '<strong>❌ Database Error:</strong><br>';
                echo htmlspecialchars($e->getMessage());
                echo '</div>';
            }
        }
        ?>
        
        <div class="info" style="margin-top: 30px;">
            <strong>ℹ️ Note:</strong><br>
            This script will:
            <ul>
                <li>Delete all records from <code>book_references</code> table</li>
                <li>Delete all records from <code>courses</code> table</li>
                <li>Reset the AUTO_INCREMENT counters to 1</li>
                <li>Preserve the table structure (columns, indexes, constraints)</li>
            </ul>
        </div>
    </div>
</body>
</html>





