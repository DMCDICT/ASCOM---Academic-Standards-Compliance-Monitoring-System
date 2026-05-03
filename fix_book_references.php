<?php
/**
 * Migration script to fix missing columns in book_references table
 * Run this by visiting: http://your-domain/fix_book_references.php
 */

require_once 'bootstrap/database.php';

try {
    $pdo = ascom_get_pdo();
    echo "<h1>Database Migration: book_references</h1>";
    
    // 1. Check for publication_year
    if (!ascom_table_has_column($pdo, 'book_references', 'publication_year')) {
        echo "Adding 'publication_year' column...<br>";
        
        // If copyright_year exists, we might want to rename it or copy from it
        if (ascom_table_has_column($pdo, 'book_references', 'copyright_year')) {
            echo "Found 'copyright_year', copying data and renaming/adding...<br>";
            $pdo->exec("ALTER TABLE book_references ADD COLUMN publication_year VARCHAR(10) NULL AFTER publisher");
            $pdo->exec("UPDATE book_references SET publication_year = copyright_year");
            echo "Data copied from 'copyright_year' to 'publication_year'.<br>";
        } else {
            $pdo->exec("ALTER TABLE book_references ADD COLUMN publication_year VARCHAR(10) NULL AFTER publisher");
            echo "Added 'publication_year' column.<br>";
        }
    } else {
        echo "'publication_year' column already exists.<br>";
    }

    // 2. Check for other essential columns
    $additional_columns = [
        'location' => "VARCHAR(255) NULL AFTER edition",
        'call_number' => "VARCHAR(100) NULL AFTER location",
        'no_of_copies' => "INT(11) DEFAULT 1 AFTER isbn",
        'processing_status' => "ENUM('processing', 'completed', 'drafted') DEFAULT 'processing' AFTER call_number",
        'status_reason' => "TEXT NULL AFTER processing_status",
        'created_by' => "INT(11) NULL AFTER status_reason",
        'requested_by' => "INT(11) NULL AFTER created_by",
        'updated_at' => "TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at"
    ];

    foreach ($additional_columns as $col => $definition) {
        if (!ascom_table_has_column($pdo, 'book_references', $col)) {
            echo "Adding '$col' column...<br>";
            $pdo->exec("ALTER TABLE book_references ADD COLUMN $col $definition");
            echo "Added '$col' column.<br>";
        } else {
            echo "'$col' column already exists.<br>";
        }
    }

    // 3. Handle book_title vs title
    if (!ascom_table_has_column($pdo, 'book_references', 'book_title') && ascom_table_has_column($pdo, 'book_references', 'title')) {
        echo "Renaming 'title' to 'book_title'...<br>";
        $pdo->exec("ALTER TABLE book_references CHANGE COLUMN title book_title VARCHAR(255) NOT NULL");
        echo "Renamed 'title' to 'book_title'.<br>";
    }

    echo "<h2>Migration completed successfully!</h2>";
    echo "<p><a href='librarian/content.php'>Back to Librarian Portal</a></p>";

} catch (Exception $e) {
    echo "<h2 style='color:red'>Migration failed!</h2>";
    echo "<pre>" . $e->getMessage() . "</pre>";
}
