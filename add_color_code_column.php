<?php
/**
 * Fix missing color_code column in programs table
 * Run this script once to add the missing column
 */

// Database connection parameters - adjust as needed
$host = 'localhost';
$dbname = 'ascom_db';
$username = 'root';
$password = ''; // Update with your password if needed

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if color_code column exists
    $stmt = $pdo->query("DESCRIBE programs");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('color_code', $columns)) {
        // Add the missing column
        $alterQuery = "ALTER TABLE programs ADD COLUMN color_code VARCHAR(7) NOT NULL DEFAULT '#00674b' AFTER created_by";
        $pdo->exec($alterQuery);
        echo "Successfully added color_code column to programs table\n";

        // Update existing rows with department colors
        $updateQuery = "
            UPDATE programs p
            JOIN departments d ON p.department_id = d.id
            SET p.color_code = COALESCE(d.color_code, '#00674b')
            WHERE p.color_code = '#00674b' OR p.color_code IS NULL
        ";
        $pdo->exec($updateQuery);
        echo "Updated existing programs with department colors\n";
    } else {
        echo "color_code column already exists in programs table\n";
    }

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage() . "\n");
}