<?php
$host = 'localhost';
$dbname = 'ascom_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    // Don't echo errors directly - let the calling script handle them
    // This prevents HTML output before JSON headers
    error_log("Database connection failed: " . $e->getMessage());
    throw new Exception("Database connection failed");
}
?> 