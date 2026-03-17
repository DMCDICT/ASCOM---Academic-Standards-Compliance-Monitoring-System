<?php
// test_database_structure.php
// Test to check the actual database structure and data

require_once dirname(__FILE__) . '/../session_config.php';
require_once 'includes/db_connection.php';

// Ensure session configuration is applied before starting session
if (session_status() == PHP_SESSION_NONE) {
    session_name('ASCOM_SESSION');
    session_set_cookie_params([
        'lifetime' => 30 * 24 * 60 * 60, // 30 days
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

echo "<h2>Database Structure Test</h2>";

try {
    // Check what tables exist
    echo "<h3>Available Tables:</h3>";
    $tablesQuery = "SHOW TABLES";
    $tablesStmt = $pdo->prepare($tablesQuery);
    $tablesStmt->execute();
    $tables = $tablesStmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>" . $table . "</li>";
    }
    echo "</ul>";
    
    // Check school_years table structure and data
    echo "<h3>School Years Table:</h3>";
    try {
        $yearsQuery = "SELECT * FROM school_years ORDER BY id DESC LIMIT 5";
        $yearsStmt = $pdo->prepare($yearsQuery);
        $yearsStmt->execute();
        $years = $yearsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($years) > 0) {
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>ID</th><th>Label</th><th>Start Date</th><th>End Date</th><th>Status/Active</th></tr>";
            foreach ($years as $year) {
                echo "<tr>";
                echo "<td>" . $year['id'] . "</td>";
                echo "<td>" . ($year['school_year_label'] ?? $year['year_label'] ?? 'N/A') . "</td>";
                echo "<td>" . ($year['start_date'] ?? 'N/A') . "</td>";
                echo "<td>" . ($year['end_date'] ?? 'N/A') . "</td>";
                echo "<td>" . ($year['status'] ?? $year['is_active'] ?? 'N/A') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "No school years found!";
        }
    } catch (Exception $e) {
        echo "Error with school_years: " . $e->getMessage();
    }
    
    // Check terms table structure and data
    echo "<h3>Terms Table:</h3>";
    try {
        $termsQuery = "SELECT * FROM terms ORDER BY id DESC LIMIT 5";
        $termsStmt = $pdo->prepare($termsQuery);
        $termsStmt->execute();
        $terms = $termsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($terms) > 0) {
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>ID</th><th>Name</th><th>School Year ID</th><th>Start Date</th><th>End Date</th><th>Active</th></tr>";
            foreach ($terms as $term) {
                echo "<tr>";
                echo "<td>" . $term['id'] . "</td>";
                echo "<td>" . ($term['name'] ?? 'N/A') . "</td>";
                echo "<td>" . $term['school_year_id'] . "</td>";
                echo "<td>" . $term['start_date'] . "</td>";
                echo "<td>" . $term['end_date'] . "</td>";
                echo "<td>" . ($term['is_active'] ? 'Yes' : 'No') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "No terms found!";
        }
    } catch (Exception $e) {
        echo "Error with terms: " . $e->getMessage();
    }
    
    // Check if school_terms table exists
    echo "<h3>School Terms Table (if exists):</h3>";
    try {
        $schoolTermsQuery = "SELECT * FROM school_terms ORDER BY id DESC LIMIT 5";
        $schoolTermsStmt = $pdo->prepare($schoolTermsQuery);
        $schoolTermsStmt->execute();
        $schoolTerms = $schoolTermsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($schoolTerms) > 0) {
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>ID</th><th>Title</th><th>School Year ID</th><th>Start Date</th><th>End Date</th><th>Status</th></tr>";
            foreach ($schoolTerms as $term) {
                echo "<tr>";
                echo "<td>" . $term['id'] . "</td>";
                echo "<td>" . ($term['title'] ?? 'N/A') . "</td>";
                echo "<td>" . $term['school_year_id'] . "</td>";
                echo "<td>" . $term['start_date'] . "</td>";
                echo "<td>" . $term['end_date'] . "</td>";
                echo "<td>" . ($term['status'] ?? 'N/A') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "No school_terms found!";
        }
    } catch (Exception $e) {
        echo "School_terms table doesn't exist or error: " . $e->getMessage();
    }
    
    // Check courses table structure
    echo "<h3>Courses Table Structure:</h3>";
    try {
        $coursesQuery = "DESCRIBE courses";
        $coursesStmt = $pdo->prepare($coursesQuery);
        $coursesStmt->execute();
        $coursesStructure = $coursesStmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        foreach ($coursesStructure as $field) {
            echo "<tr>";
            echo "<td>" . $field['Field'] . "</td>";
            echo "<td>" . $field['Type'] . "</td>";
            echo "<td>" . $field['Null'] . "</td>";
            echo "<td>" . $field['Key'] . "</td>";
            echo "<td>" . ($field['Default'] ?? 'NULL') . "</td>";
            echo "<td>" . $field['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } catch (Exception $e) {
        echo "Error with courses table: " . $e->getMessage();
    }
    
} catch (Exception $e) {
    echo "Database error: " . $e->getMessage();
}
?>
