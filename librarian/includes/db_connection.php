<?php
$host = 'localhost';
$dbname = 'ascom_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    // Set charset to ensure proper encoding
    $pdo->exec("set names utf8mb4");
} catch(PDOException $e) {
    // Log error instead of echoing to prevent HTML output in AJAX responses
    error_log("Database connection failed: " . $e->getMessage());
    throw new Exception("Database connection failed");
}
?> 